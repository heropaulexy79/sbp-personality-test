<script lang="ts" setup>
import { Course, OrganisationUser, User } from "@/types";
import axios from "axios";
import { computed, onMounted, ref, watch } from "vue"; // <-- ADD 'watch'
import { debounce } from "lodash"; // <-- ADD debounce utility (requires lodash)
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
import { getPublicProfileImage } from "@/lib/utils";

type Student = OrganisationUser;

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
    students.value?.filter((i) => modelValue.value.includes(i.user_id + "")),
);

const wrapperRef = ref<HTMLElement>();

const form = useForm({
    // Form data is irrelevant as it's transformed, but adding user_ids for clarity
    user_ids: [] as string[],
});

const filteredStudents = computed(() =>
    students.value?.filter((i) => !modelValue.value.includes(i.user_id + "")),
);

// MODIFIED: Accepts an optional search term and passes it to the API
async function fetchStudents(search: string = "") {
    error.value = null;
    // Don't clear students.value here, set loading indicator instead
    loading.value = true;

    try {
        students.value = (
            await axios.get(route("organisation.employees"), {
                params: {
                    search: search, // Pass the search term to the backend
                },
            })
        ).data.students;
    } catch (err: any) {
        error.value = err.toString();
    } finally {
        loading.value = false;
    }
}

// NEW: Implement debouncing to prevent excessive API calls while the user types
const debouncedFetchStudents = debounce((search: string) => {
    fetchStudents(search);
}, 300); // 300ms delay

// NEW: Watch the searchTerm and trigger the debounced fetch
watch(searchTerm, (newSearchTerm) => {
    debouncedFetchStudents(newSearchTerm);
});

// MODIFIED & FIXED: Corrected payload key to 'user_ids'
function enrollStudents() {
    form.transform(() => ({
        // FIX: Use 'user_ids' as expected by the CourseEnrollmentController
        user_ids: modelValue.value,
    })).post(route("course.enroll", { course: props.course.slug }), {
        onSuccess() {
            props.onSuccess?.();
        },
    });
}

// MODIFIED: Initial fetch runs without a search term
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
                                    :src="
                                        getPublicProfileImage(item.user.email)
                                    "
                                    class="leading-none"
                                />
                                <AvatarFallback>{{
                                    item.user.name[0]
                                }}</AvatarFallback>
                            </Avatar>
                        </span>
                        <span class="rounded bg-transparent px-2 py-1 text-sm">
                            {{ item.user.name }}
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
                                class="w-full border-none px-3 outline-hidden ring-0 focus-visible:ring-0"
                                :class="modelValue.length > 0 ? 'mt-2' : ''"
                                @keydown.enter.prevent
                            />
                        </ComboboxInput>
                    </ComboboxAnchor>

                    <ComboboxPortal :to="wrapperRef">
                        <CommandList
                            position="popper"
                            class="mt-2 w-[--radix-popper-anchor-width] rounded-md border bg-popover text-popover-foreground shadow-md outline-hidden data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2"
                            dismissable
                        >
                            <CommandEmpty> No Students </CommandEmpty>
                            <CommandGroup>
                                <CommandItem disabled value="" v-if="loading">
                                    Loading...
                                </CommandItem>
                                <CommandItem
                                    v-for="student in filteredStudents"
                                    :key="student.id"
                                    :value="student.user.name"
                                    @select.prevent="
                                        (ev) => {
                                            modelValue.push(
                                                student.user_id + '',
                                            );

                                            if (filteredStudents.length === 0) {
                                                open = false;
                                            }
                                        }
                                    "
                                    class="flex items-center gap-2"
                                >
                                    <Avatar class="size-6">
                                        <AvatarImage
                                            :src="
                                                getPublicProfileImage(
                                                    student.user.email,
                                                )
                                            "
                                        />
                                        <AvatarFallback>{{
                                            student.user.name[0]
                                        }}</AvatarFallback>
                                    </Avatar>
                                    <span>{{ student.user.name }}</span>
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