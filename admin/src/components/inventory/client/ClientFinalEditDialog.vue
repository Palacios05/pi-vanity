<script setup>
import REGIONES from '@/assets/json/paises.json'
import PROVINCIAS from '@/assets/json/departamentos.json'
import DISTRITOS from '@/assets/json/ciudades.json'

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  clientSelected: {
    type: Object,
    required: true,
  }
})

const emit = defineEmits([
  'update:isDialogVisible',
  'editClient'
])
const full_name = ref(null);
const email  = ref(null);
const phone  = ref(null);
const type_document  = ref('DNI');
const n_document  = ref(null);
const address  = ref(null);
const name = ref(null);
const surname = ref(null);
const type_client = ref(1);
const birth_date = ref(null);
const gender = ref("M");
const ubigeo_region = ref(null);
const ubigeo_provincia = ref(null);
const ubigeo_distrito = ref(null);
const region = ref(null);
const provincia = ref(null);
const distrito = ref(null);
const state = ref(1);

const REGIONES_L = REGIONES;
const PROVINCIAS_L = PROVINCIAS;
const DISTRITOS_L = DISTRITOS;

const PROVINCIA_SELECTS = ref([]);
const DISTRITO_SELECTS = ref([]);

const warning = ref(null);
const error_exits = ref(null);
const success = ref(null);

const update = async() => {
  warning.value = null;
  error_exits.value = null;
  success.value = null;
  if(!name.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el nombre del cliente";
    }, 50);
    return;
  }
  if(!surname.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el apellido del cliente";
    }, 50);
    return;
  }
  if(!phone.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el telefono del cliente";
    }, 50);
    return;
  }
  if(!n_document.value){
    setTimeout(() => {
      warning.value = "Se debe llenar el n° de document del cliente";
    }, 50);
    return;
  }
  if(!type_document.value){
    setTimeout(() => {
      warning.value = "Se debe un tipo de documento para el cliente";
    }, 50);
    return;
  }
  if(!ubigeo_region.value){
    setTimeout(() => {
      warning.value = "Se debe seleccionar una region para el cliente";
    }, 50);
    return;
  }
  if(!ubigeo_provincia.value){
    setTimeout(() => {
      warning.value = "Se debe seleccionar una provincia para el cliente";
    }, 50);
    return;
  }
  if(!ubigeo_distrito.value){
    setTimeout(() => {
      warning.value = "Se debe debe seleccionar un distrito para el cliente";
    }, 50);
    return;
  }
  if(!address.value){
    setTimeout(() => {
      warning.value = "Se debe debe una dirección para el cliente";
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
    name: name.value,
    surname: surname.value,
    full_name: name.value+' '+surname.value,
    email: email.value,
    phone: phone.value,
    type_document: type_document.value,
    n_document: n_document.value,
    address: address.value,
    birth_date: birth_date.value,
    gender: gender.value,
    ubigeo_region: ubigeo_region.value,
    ubigeo_provincia: ubigeo_provincia.value,
    ubigeo_distrito: ubigeo_distrito.value,
    region: region.value,
    provincia: provincia.value,
    distrito: distrito.value,
    type_client:type_client.value,
    state: state.value,
  }

  try {
    const resp = await $api("clients/"+props.clientSelected.id,{
      method:'PATCH',
      body: data,
      onResponseError({response}){
        error_exits.value = response._data.error;
      }
    })
    console.log(resp);
    if(resp.message == 403){
      error_exits.value = resp.message_text;
    }else{
      success.value = "El cliente se ha editado correctamente";
      emit("editClient",resp.client);
      warning.value = null;
      error_exits.value = null;
    }
  } catch (error) {
    console.log(error);
  }
}

onMounted(() => {
  console.log(REGIONES_L,PROVINCIAS_L,DISTRITOS_L);
  name.value = props.clientSelected.name;
  surname.value = props.clientSelected.surname;
  email.value = props.clientSelected.email;
  full_name.value = props.clientSelected.full_name;
  phone.value = props.clientSelected.phone;
  type_document.value = props.clientSelected.type_document;
  n_document.value = props.clientSelected.n_document;
  address.value = props.clientSelected.address;
  birth_date.value = props.clientSelected.birth_date;
  ubigeo_region.value = props.clientSelected.ubigeo_region;
  ubigeo_provincia.value = props.clientSelected.ubigeo_provincia;
  ubigeo_distrito.value = props.clientSelected.ubigeo_distrito;
  type_client.value = props.clientSelected.type_client;
  state.value = props.clientSelected.state;
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
            Edit Client Final : {{ props.clientSelected.id }}
          </h4>
        </div>

        <!-- 👉 Form -->
        <VForm
          class="mt-4"
          @submit.prevent="update"
        >
          <VRow>
            <!-- 👉 First Name -->
            <VCol
              cols="6"
            >
              <VTextField
                v-model="name"
                label="Nombre del Cliente"
                placeholder="Example: Jose"
              />
            </VCol>

            <VCol
              cols="6"
            >
              <VTextField
                v-model="surname"
                label="Apellido del Cliente"
                placeholder="Example: Jose"
              />
            </VCol>
            
            <VCol
              cols="6"
            >
                <VSelect
                    :items="[
                        {
                        id: 1,
                        name: 'Cliente Final'
                        },
                    ]"
                    item-title="name"
                    item-value="id"
                    placeholder="Select"
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
    'Cédula de Ciudadanía',
    'Cédula de Extranjería',
    'Pasaporte',
    'Permiso por Protección Temporal (PPT)',
    'Visa'
]"
                    v-model="type_document"
                    label="Tipo de documento"
                    placeholder="Select Item"
                    eager
                />
            </VCol>

            <VCol
              cols="5"
            >
              <VTextField
                v-model="n_document"
                type="number"
                label="N° Document"
                placeholder="Example: 9999999"
              />
            </VCol>

            <VCol
              cols="4"
            >
              <VRadioGroup v-model="gender">
                <VRadio
                  label="Masculino"
                  value="M"
                />
                <VRadio
                    label="Femenino"
                    value="F"
                />
              </VRadioGroup>
            </VCol>

            <VCol
              cols="3"
            >
                <VSelect
                    :items="[
                        {
                            name: 'Activo',
                            id: 1,
                        },
                        {
                            name: 'Inactivo',
                            id: 2,
                        },
                    ]"
                    item-title="name"
                    item-value="id"
                    v-model="state"
                    label="Estado"
                    placeholder="Select Item"
                    eager
                />
            </VCol>

            <VCol
              cols="6"
            >
                <label for="">Fecha de nacimiento</label>
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
              cols="4"
            >
                <VSelect
                    :items="REGIONES_L"
                    v-model="ubigeo_region"
                    item-title="name"
                    item-value="id"
                    label="Pais"
                    placeholder="Select Item"
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
                    label="Provincia"
                    placeholder="Select Item"
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
                    label="Distrito"
                    placeholder="Select Item"
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
                Editar
              </VBtn>

              <VBtn
                color="secondary"
                variant="outlined"
                @click="onFormReset"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
