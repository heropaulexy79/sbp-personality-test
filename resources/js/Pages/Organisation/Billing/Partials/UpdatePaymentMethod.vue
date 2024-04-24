<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { errorBagToString } from "@/lib/errors";
import { PaymentMethod } from "@/types";
import { useForm, usePage } from "@inertiajs/vue3";
import { useScriptTag } from "@vueuse/core";
import { toast } from "vue-sonner";

const page = usePage();

const props = defineProps<{
    payment_method: PaymentMethod | null;
}>();

useScriptTag(
    "https://js.paystack.co/v1/inline.js",
    // on script tag loaded.
    (el: HTMLScriptElement) => {
        // do something
        console.log(el);
    },
);

const payForm = useForm({
    email: page.props.auth.user.email,
    amount: 100 * 100,
    currency: "NGN",
    metadata: {
        type: "ADD-PAYMENT-METHOD",
        organisation_id: page.props.auth.user.organisation_id,
    },
});

function addMethod() {
    payForm.post(route("paystack.pay"), {
        onSuccess() {},
        onError(errors) {
            toast.error("Cancel invite", {
                description: errorBagToString(errors),
            });
        },
    });
}
function addMethod2() {
    let handler = window.PaystackPop.setup({
        key: "pk_test_xxxxxxxxxx", // Replace with your public key
        email: page.props.auth.user.email,
        amount: 100 * 100,
        // ref: "" + Math.floor(Math.random() * 1000000000 + 1), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
        // // label: "Optional string that replaces customer email"
        onClose: function () {
            console.log("Window closed.");
        },
        callback: function (response: any) {
            console.log(response);
            let message = "Payment complete! Reference: " + response.reference;
            alert(message);
        },
    });

    handler.openIframe();
}
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

        <form class="mt-2" @submit.prevent="addMethod">
            <Button class="mt-3">Update payment method</Button>
        </form>
    </div>
    <form v-else class="mt-2" @submit.prevent="addMethod">
        <div class="text-sm text-muted-foreground">
            No payment methods! Setup one bellow
        </div>
        <Button class="mt-3">Add payment method</Button>
    </form>
</template>
