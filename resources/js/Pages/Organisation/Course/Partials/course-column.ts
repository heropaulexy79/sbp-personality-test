import { Badge } from "@/Components/ui/badge";
import { Course } from "@/types";
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
                        class: "inline-flex gap-4 items-center justify-center",
                        href: route("course.show", {
                            course: props.row.original.id,
                        }),
                    },
                    () => [
                        h("div", {
                            style: {
                                backgroundImage: props.row.original.banner_image
                                    ? `url(${props.row.original.banner_image})`
                                    : "",
                            },
                            class: "size-12 rounded-md overflow-hidden bg-gray-400 bg-cover",
                        }),
                        props.row.getValue("title"),
                    ],
                ),
            );
        },
    }),
    columnHelper.display({
        id: "is_published",
        header: () => h("div", { class: "" }, "Status"),
        cell(props) {
            const isPublished = props.row.original.is_published;
            return h(
                "div",
                { class: "" },
                h(
                    Badge,
                    {
                        variant: isPublished ? "default" : "outline",
                        class: "uppercase",
                    },
                    () => (isPublished ? "Published" : "Draft"),
                ),
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
