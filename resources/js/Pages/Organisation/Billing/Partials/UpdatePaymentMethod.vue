<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { PaymentMethod } from "@/types";

const props = defineProps<{
    payment_method: PaymentMethod | null;
}>();
</script>

<template>
    <header>
        <h2 class="text-lg font-medium">Payment methods</h2>

        <p class="mt-1 text-sm text-muted-foreground">
            Manage your payment methods to pay your subscription securely.
        </p>
    </header>

    <div className="flex flex-col gap-2 mt-2" v-if="payment_method">
        <div className="grid grid-cols-2 items-center text-sm">
            <div>Debit card ({{ payment_method.card_type }})</div>
            <div className="text-right">
                **** **** **** {{ payment_method.last_four }}
            </div>
        </div>
        <div className="grid grid-cols-2 items-center text-sm">
            <div>Expires</div>
            <div className="text-right">{{ payment_method.exp }}</div>
        </div>
    </div>
    <form v-else class="mt-2" @submit.prevent="() => console.log('hi')">
        <div class="text-sm text-muted-foreground">
            No payment methods! Setup one bellow
        </div>
        <Button class="mt-3">Add payment method</Button>
    </form>
</template>
