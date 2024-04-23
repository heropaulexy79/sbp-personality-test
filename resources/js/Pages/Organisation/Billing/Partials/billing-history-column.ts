import { BillingHistory } from "@/types";
import { createColumnHelper } from "@tanstack/vue-table";
import { h } from "vue";

const columnHelper = createColumnHelper<BillingHistory>();

export const billingHistoryColumns = [
    columnHelper.accessor("transaction_ref", {
        header: () => h("div", { class: "" }, "Transaction reference"),
        cell(props) {
            return h(
                "div",
                { class: "" },
                props.row.getValue("transaction_ref"),
            );
        },
    }),

    // TODO: DELETE MEMBERSHIP
    // columnHelper.display({
    //     id: 'actions',
    //     enableHiding: false,
    //     cell: ({ row }) => {
    //       const payment = row.original

    //       return h('div', { class: 'relative' }, h(DropdownAction, {
    //         payment,
    //       }))
    //     },
    //   }),
];
