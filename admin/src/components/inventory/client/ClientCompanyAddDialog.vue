<script setup>
import REGIONES from '@/assets/json/paises.json'
import PROVINCIAS from '@/assets/json/departamentos.json'
import DISTRITOS from '@/assets/json/ciudades.json'

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'addClient'
])
const full_name = ref(null);
const email  = ref(null);
const phone  = ref(null);
const type_document  = ref('RUC');
const n_document  = ref(null);
const address  = ref(null);
const type_client = ref(2);
const birth_date = ref(null);
const ubigeo_region = ref('01');
const ubigeo_provincia = ref(null);
const ubigeo_distrito = ref(null);
const region = ref(null);
const provincia = ref(null);
const distrito = ref(null);

const REGIONES_L = REGIONES;
const PROVINCIAS_L = PROVINCIAS;
const DISTRITOS_L = DISTRITOS;

const PROVINCIA_SELECTS = ref(PROVINCIAS.filter((p) => p.department_id == '01'));
const DISTRITO_SELECTS = ref([]);

const warning = ref(null);
const error_exits = ref(null);
const success = ref(null);

const store = async() => {
  warning.value = null;
  error_exits.value = null;
  success.value = null;
  if(!full_name.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el nombre de la empresa";
    }, 50);
    return;
  }
  if(!phone.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el telefono de la empresa";
    }, 50);
    return;
  }
  if(!n_document.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el n° de documento de la empresa";
    }, 50);
    return;
  }
  if(!type_document.value){
    setTimeout(() => {
      warning.value = "Se debe seleccionar un tipo de documento de la empresa";
    }, 50);
    return;
  }
  if(!ubigeo_region.value){
    setTimeout(() => {
      warning.value = "Se debe seleccionar un país de la empresa";
    }, 50);
    return;
  }
  if(!ubigeo_provincia.value){
    setTimeout(() => {
      warning.value = "Se debe seleccionar un departamento de la empresa";
    }, 50);
    return;
  }
  if(!ubigeo_distrito.value){
    setTimeout(() => {
      warning.value = "Se debe seleccionar una ciudad de la empresa";
    }, 50);
    return;
  }
  if(!address.value){
    setTimeout(() => {
      warning.value = "Se debe ingresar una dirección de la empresa";
    }, 50);
    return;
  }

  let REGION_SELECTED = REGIONES_L.find((region) => region.id == ubigeo_region.value);
  if(REGION_SELECTED){
    region.value = REGION_SELECTED.name;
  }
  let PROVINCIA_SELECTED = PROVINCIAS_L.find((provincia) => provincia.id == ubigeo_provincia.value);
  if(PROVINCIA_SELECTED){
    provincia.value = PROVINCIA_SELECTED.name;
  }
  let DISTRITO_SELECTED = DISTRITOS_L.find((distrito) => distrito.id == ubigeo_distrito.value);
  if(DISTRITO_SELECTED){
    distrito.value = DISTRITO_SELECTED.name;
  }

  let data = {
    full_name: full_name.value,
    email: email.value,
    phone: phone.value,
    type_document: type_document.value,
    n_document: n_document.value,
    address: address.value,
    birth_date: birth_date.value,
    ubigeo_region: ubigeo_region.value,
    ubigeo_provincia: ubigeo_provincia.value,
    ubigeo_distrito: ubigeo_distrito.value,
    region: region.value,
    provincia: provincia.value,
    distrito: distrito.value,
    type_client:type_client.value,
    state: 1,
  }

  try {
    const resp = await $api("clients",{
      method:'POST',
      body: data,
      onResponseError({response}){
        error_exits.value = response._data.error;
      }
    })
    console.log(resp);
    if(resp.message == 403){
      error_exits.value = resp.message_text;
    }else{
      success.value = "El cliente se ha registrado correctamente";
      emit("addClient",resp.client);
      full_name.value = '';
      email.value = '';
      phone.value = '';
      type_document.value = 'RUC';
      n_document.value = '';
      address.value = '';
      ubigeo_region.value = '01';
      ubigeo_provincia.value = '';
      ubigeo_distrito.value = '';
      birth_date.value = null;
      warning.value = null;
      error_exits.value = null;
      success.value = null;
      onFormReset();
    }
  } catch (error) {
    console.log(error);
  }
}

onMounted(() => {
  console.log(REGIONES_L,PROVINCIAS_L,DISTRITOS_L);
})

watch(ubigeo_region,(value) => {
  console.log(value);
  PROVINCIA_SELECTS.value = PROVINCIAS_L.filter((provincia) => provincia.department_id == value);
})
watch(ubigeo_provincia,(value) => {
  console.log(value);
  DISTRITO_SELECTS.value = DISTRITOS_L.filter((distrito) => distrito.province_id == value);
})
const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', userData.value)
}

const onFormReset = () => {
  emit('update:isDialogVisible', false)
}

const dialogVisibleUpdate = val => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    max-width="650"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogVisibleUpdate"
  >
    <VCard class="pa-sm-11 pa-3">
      <!-- 👉 dialog close btn -->
      <DialogCloseBtn
        variant="text"
        size="default"
        @click="onFormReset"
      />

      <VCardText class="pt-5">
        <div class="text-center pb-6">
          <h4 class="text-h4 mb-2">
            Agregar cliente empresa
          </h4>
        </div>

        <!-- 👉 Form -->
        <VForm
          class="mt-4"
          @submit.prevent="store"
        >
          <VRow>
            <!-- 👉 First Name -->
            <VCol
              cols="10"
            >
              <VTextField
                v-model="full_name"
                label="Nombre de la Empresa"
                placeholder="Example: Jose"
              />
            </VCol>
            
            <VCol
              cols="6"
            >
                <VSelect
                    :items="[
                        {
                        id: 2,
                        name: 'Cliente Empresa'
                        },
                    ]"
                    item-title="name"
                    item-value="id"
                    placeholder="Seleccionar"
                    label="Tipo de cliente"
                    v-model="type_client"
                    />
            </VCol>

            <VCol
              cols="6"
            >
              <VTextField
                v-model="email"
                label="Correo"
                placeholder="Example: vanity@gmail.com"
              />
            </VCol>

            <VCol
              cols="6"
            >
              <VTextField
                v-model="phone"
                type="number"
                label="Telefono"
                placeholder="Example: 9999999"
              />
            </VCol>

            <VCol
              cols="6"
            >
                <VSelect
                        :items="[
        'NIT (Número de Identificación Tributaria)'
    ]"
                    v-model="type_document"
                    label="Tipo de documento"
                    placeholder="Seleccionar"
                    eager
                />
            </VCol>

            <VCol
              cols="6"
            >
              <VTextField
                v-model="n_document"
                type="number"
                label="N° Document"
                placeholder="Example: 9999999"
              />
            </VCol>

            <VCol
              cols="6"
            >
                <label for="">Fecha de Incorporacion</label>
                <div class="app-picker-field">
                    <div class="v-input v-input--horizontal v-input--center-affix v-input--density-comfortable v-locale--is-ltr position-relative v-text-field">
                        <div class="v-input__control">
                            <div class="v-field v-field--center-affix v-field--variant-outlined v-theme--light v-locale--is-ltr">
                                <div class="v-field__field">
                                    <div class="v-field__input">
                                        <input type="date" class="flat-picker-custom-style flatpickr-input" v-model="birth_date" style="opacity: 1;"  id="">
                                    </div>
                                </div>
                                <div class="v-field__outline text-primary"><div class="v-field__outline__start"></div><div class="v-field__outline__notch"><label class="v-label v-field-			label v-field-label--floating" aria-hidden="true" for="input-8" style="">Nombre</label></div><div class="v-field__outline__end"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </VCol>

            <VCol
              cols="6"
            >
                <VTextarea
                    v-model="address"
                    label="Dirección: "
                    placeholder=""
                />

            </VCol>
            <VCol
              cols="6"
            ></VCol>
            <VCol
              cols="4"
            >
                <VSelect
                    :items="REGIONES_L"
                    v-model="ubigeo_region"
                    readonly
                    item-title="name"
                    item-value="id"
                    label="País"
                    placeholder="Seleccionar"
                    eager
                />
            </VCol>
            <VCol
              cols="4"
            >
                <VSelect
                    :items="PROVINCIA_SELECTS"
                    v-model="ubigeo_provincia"
                    item-title="name"
                    item-value="id"
                    label="Departamento"
                    placeholder="Seleccionar"
                    eager
                />
            </VCol>
            <VCol
              cols="4"
            >
                <VSelect
                    :items="DISTRITO_SELECTS"
                    v-model="ubigeo_distrito"
                    item-title="name"
                    item-value="id"
                    label="Ciudad"
                    placeholder="Seleccionar"
                    eager
                />
            </VCol>
            

            <VCol
              cols="12"
              v-if="warning"
            >
              <VAlert
                closable
                close-label="Close Alert"
                color="warning"
              >
              {{ warning }}
              </VAlert>
            </VCol>
            <VCol
              cols="12"
              v-if="error_exits"
            >
              <VAlert
                closable
                close-label="Close Alert"
                color="error"
              >
              {{ error_exits }}
              </VAlert>
            </VCol>
            <VCol
              cols="12"
              v-if="success"
            >
              <VAlert
                closable
                close-label="Close Alert"
                color="success"
              >
              {{ success }}
              </VAlert>
            </VCol>
            <VCol
              cols="12"
            >

            </VCol>
            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Guardar
              </VBtn>

              <VBtn
                color="secondary"
                variant="outlined"
                @click="onFormReset"
              >
                Cancelar
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
