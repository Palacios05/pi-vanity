<script setup>

  const sale_total_payment_complete_month_current = ref(0);
  const variation_percentage_sale_total_payment_complete = ref(0);
  const percentage_sale_payment_complete = ref(0);
  const num_sales_payment_total_month_current = ref(0);
  const percentage_sale_pending = ref(0);
  const num_sales_payment_pending_month_current = ref(0);
  const sales_payment_total_pending = async() => {
    try {
        const resp = await $api("kpi/sales_payment_total_pending",{
            method: 'POST',
            body:{},
            onResponseError({response}){
                console.log(response._data.error);
            }
        })
        console.log(resp);
        sale_total_payment_complete_month_current.value = resp.sale_total_payment_complete_month_current;
        variation_percentage_sale_total_payment_complete.value = resp.variation_percentage_sale_total_payment_complete;
        percentage_sale_payment_complete.value = resp.percentage_sale_payment_complete;
        num_sales_payment_total_month_current.value = resp.num_sales_payment_total_month_current;
        percentage_sale_pending.value = resp.percentage_sale_pending;
        num_sales_payment_pending_month_current.value = resp.num_sales_payment_pending_month_current;
      } catch (error) {
      console.log(error);
    }
  }

  onMounted(() => {
    sales_payment_total_pending();
  })
</script>
<template>
  <VCard>
    <VCardText>
      <div class="d-flex align-center justify-space-between">
        <div class="text-body-1">
          Total Visits
        </div>
        <div class="d-flex justify-center" v-if="variation_percentage_sale_total_payment_complete >= 0">
          <span class="text-success">+{{variation_percentage_sale_total_payment_complete}}%</span>
          <VIcon
            icon="ri-arrow-up-s-line"
            color="success"
            size="20"
          />
        </div>
        <div class="d-flex justify-center" v-if="variation_percentage_sale_total_payment_complete < 0">
          <span class="text-danger">-{{variation_percentage_sale_total_payment_complete}}%</span>
          <VIcon
            icon="ri-arrow-down-s-line"
            color="danger"
            size="20"
          />
        </div>
      </div>
      <h4 class="text-h4">
        $ {{ sale_total_payment_complete_month_current }}
      </h4>
    </VCardText>

    <VCardText>
      <VRow no-gutters>
        <VCol cols="5">
          <div class="d-flex align-center mb-2">
            <VAvatar
              rounded
              color="warning"
              variant="tonal"
              :size="24"
              class="me-2"
            >
              <VIcon
                size="16"
                icon="ri-pie-chart-2-line"
              />
            </VAvatar>

            <span>Pagos Completos</span>
          </div>
          <h4 class="text-h4 mb-2">
            {{percentage_sale_payment_complete}}%
          </h4>
          <div class="text-body-1">
            {{ num_sales_payment_total_month_current }}
          </div>
        </VCol>

        <VCol cols="2">
          <div class="d-flex flex-column justify-center align-center h-100">
            <VDivider
              vertical
              class="mx-auto mb-2"
            />
            <VAvatar
              size="28"
              variant="tonal"
              class="text-body-2"
            >
              VS
            </VAvatar>
            <VDivider
              vertical
              class="mx-auto mt-2"
            />
          </div>
        </VCol>

        <VCol
          cols="5"
          class="text-end"
        >
          <div class="d-flex align-center justify-end mb-2">
            <span class="me-2">Pagos Pendientes</span>

            <VAvatar
              rounded="sm"
              color="primary"
              variant="tonal"
              :size="24"
            >
              <VIcon
                size="16"
                icon="ri-mac-line"
              />
            </VAvatar>
          </div>
          <h4 class="text-h4 mb-2">
            {{percentage_sale_pending}}%
          </h4>
          <div class="text-body-1">
            {{ num_sales_payment_pending_month_current }}
          </div>
        </VCol>
      </VRow>

      <div class="mt-4">
        <VProgressLinear
          :model-value="percentage_sale_payment_complete"
          color="warning"
          bg-color="primary"
          bg-opacity="1"
          :rounded-bar="false"
          height="8"
          rounded
        />
      </div>
    </VCardText>
  </VCard>
</template>
