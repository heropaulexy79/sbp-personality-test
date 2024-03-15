import { Course, OrganisationInvite, User } from "@/types";
import { Link } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { h } from "vue";

const columnHelper = createColumnHelper<Course>();

export const courseColumns = [
    columnHelper.accessor("title", {
        header: () => h("div", { class: "" }, "Title"),
        cell(props) {
            return h(
                "div",
                { class: "" },
                h(
                    Link,
                    {
                        href: route("course.show", {
                            course: props.row.original.id,
                        }),
                    },
                    props.row.getValue("title")
                )
            );
        },
    }),

    // columnHelper.accessor("role", {
    //     header: () => h("div", { class: "" }, "Role"),
    //     cell(props) {
    //         return h(
    //             "div",
    //             { class: "" },
    //             h(SelectTeamRole, {
    //                 organisation_id: props.row.original.organisation_id!,
    //                 role: props.row.getValue("role") as User["role"],
    //                 user_id: props.row.original.id!,
    //             })
    //         );
    //     },
    //     size: 200,
    // }),

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
