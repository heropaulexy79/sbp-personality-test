<script lang="ts" setup>
import { Course, User } from "@/types";
import axios from "axios";
import { computed, onMounted, ref } from "vue";
import {
    ComboboxAnchor,
    ComboboxInput,
    ComboboxPortal,
    ComboboxRoot,
} from "radix-vue";
import {
    CommandEmpty,
    CommandGroup,
    CommandItem,
    CommandList,
} from "@/Components/ui/command";
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from "@/Components/ui/tags-input";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Button } from "@/Components/ui/button";
import { useForm } from "@inertiajs/vue3";

type Student = Pick<User, "id" | "name" | "email">;

const props = defineProps<{
    course: Course;
    onSuccess: () => void;
}>();

const loading = ref(false);
const students = ref<Student[]>([]);
const error = ref(null);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref("");
const seletedStudents = computed(() =>
    students.value?.filter((i) => modelValue.value.includes(i.id + "")),
);

const wrapperRef = ref<HTMLElement>();

const form = useForm({
    // students: [] as string[],
});
// const studentsMap = computed(()=>{
//   const n = new Map<number,Student>();
//
//   students.value.forEach((r)=>{
//     n.set(r.id, r);
//   })
//
//   return n;
// });

const filteredStudents = computed(() =>
    students.value?.filter((i) => !modelValue.value.includes(i.id + "")),
);

async function fetchStudents() {
    error.value = null;
    students.value = [];
    loading.value = true;

    try {
        // replace `getPost` with your data fetching util / API wrapper
        students.value = (
            await axios.get(route("organisation.employees"))
        ).data.students;
    } catch (err: any) {
        error.value = err.toString();
    } finally {
        loading.value = false;
    }
}

function enrollStudents() {
    form.transform(() => ({
        students: modelValue.value,
    })).post(route("course.enroll", { course: props.course.slug }), {
        onSuccess() {
            props.onSuccess?.();
        },
    });
}

onMounted(async () => {
    await fetchStudents();
});
</script>

<template>
    <span v-if="error" class="my-5 text-destructive">{{ error }}</span>
    <div ref="wrapperRef">
        <form @submit.prevent="enrollStudents">
            <TagsInput class="gap-0 px-0" :model-value="modelValue">
                <div class="flex flex-wrap items-center gap-2 px-3">
                    <TagsInputItem
                        v-for="item in seletedStudents"
                        :key="item.id"
                        :value="item.id + ''"
                        class="h-7"
                    >
                        <TagsInputItemText class="sr-only" />
                        <span class="py-1 pl-2 leading-none">
                            <Avatar class="size-6 border">
                                <AvatarImage
                                    :src="`https://unavatar.io/${item.email}?ttl=1d`"
                                    class="leading-none"
                                />
                                <AvatarFallback>{{
                                    item.name[0]
                                }}</AvatarFallback>
                            </Avatar>
                        </span>
                        <span class="rounded bg-transparent px-2 py-1 text-sm">
                            {{ item.name }}
                        </span>
                        <TagsInputItemDelete />
                    </TagsInputItem>
                </div>

                <ComboboxRoot
                    v-model="modelValue"
                    v-model:open="open"
                    v-model:searchTerm="searchTerm"
                    class="relative w-full"
                >
                    <ComboboxAnchor as-child>
                        <ComboboxInput placeholder="Students..." as-child>
                            <TagsInputInput
                                class="w-full border-none px-3 outline-none ring-0 focus-visible:ring-0"
                                :class="modelValue.length > 0 ? 'mt-2' : ''"
                                @keydown.enter.prevent
                            />
                        </ComboboxInput>
                    </ComboboxAnchor>

                    <ComboboxPortal :to="wrapperRef">
                        <CommandList
                            position="popper"
                            class="mt-2 w-[--radix-popper-anchor-width] rounded-md border bg-popover text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2"
                        >
                            <CommandEmpty> No Students </CommandEmpty>
                            <CommandGroup>
                                <CommandItem disabled value="" v-if="loading">
                                    Loading...
                                </CommandItem>
                                <CommandItem
                                    v-for="student in filteredStudents"
                                    :key="student.id"
                                    :value="student.name"
                                    @select.prevent="
                                        (ev) => {
                                            // if (typeof ev.detail.value === 'string') {
                                            //     searchTerm = '';
                                            //     modelValue.value.push(ev.detail.value);
                                            // }
                                            modelValue.push(student.id + '');

                                            if (filteredStudents.length === 0) {
                                                open = false;
                                            }
                                        }
                                    "
                                    class="flex items-center gap-2"
                                >
                                    <Avatar class="size-6 border">
                                        <AvatarImage
                                            :src="`https://unavatar.io/${student.email}?ttl=1d`"
                                        />
                                        <AvatarFallback>{{
                                            student.name[0]
                                        }}</AvatarFallback>
                                    </Avatar>
                                    <span>{{ student.name }}</span>
                                </CommandItem>
                            </CommandGroup>
                        </CommandList>
                    </ComboboxPortal>
                </ComboboxRoot>
            </TagsInput>

            <Button
                type="submit"
                class="mt-4 w-full"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Enroll selected ({{ modelValue.length }})
            </Button>
        </form>
    </div>
</template>
