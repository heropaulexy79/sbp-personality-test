<script lang="ts" setup>
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import Link from "@tiptap/extension-link";
import { Placeholder } from "@tiptap/extension-placeholder";
import StarterKit from "@tiptap/starter-kit";
import Image from "@tiptap/extension-image";
import { EditorContent, useEditor, type Content } from "@tiptap/vue-3";
import { useAttrs, watch } from "vue";
import {
    EditorCommandMenu,
    editorSuggestions,
} from "./slash-menu/editor-suggestions";
import { useEditorStore } from "./use-editor-store";
import UploadMediaForm from "./components/UploadMediaForm.vue";

const attrs = useAttrs();

const props = withDefaults(
    defineProps<{
        id?: string;
        class?: string;
        disabled?: boolean;
        placeholder?: string;
    }>(),
    {
        disabled: false,
        // placeholder: "",
    },
);

const modelValue = defineModel<Content>();

const editor = useEditor({
    extensions: [
        StarterKit,
        Placeholder.configure({
            placeholder:
                props.placeholder ??
                "Write something or press / for commands...",
            emptyEditorClass: "is-editor-empty",
        }),
        // BubbleMenu.con,
        EditorCommandMenu.configure({
            // @ts-ignore
            suggestion: editorSuggestions,
        }),
        Link.configure({
            protocols: ["https", "mailto", "tel"],
        }),
        Image.configure({
            //   HTMLAttributes: {
            //     class: 'my-custom-class',
            //   },
        }),
    ],
    content: modelValue.value,
    onUpdate: ({ editor, transaction }) => {
        // HTML
        // this.$emit('update:modelValue', this.editor.getHTML())
        modelValue.value = editor.getHTML();

        // JSON
        // this.$emit('update:modelValue', this.editor.getJSON())
    },
    editable: !props.disabled,
    editorProps: {
        attributes: {
            class: "prose max-w-[unset] w-full rounded-md  bg-background px-0 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
        },
    },
});

const editorStore = useEditorStore();

watch(
    modelValue,
    (value) => {
        const edt = editor.value;
        if (!edt || !value) return;

        const isSame = edt.getHTML() === value;

        // JSON
        // const isSame = JSON.stringify(editor.getJSON()) === JSON.stringify(value)

        if (isSame) {
            return;
        }

        edt.commands.setContent(value, false);
    },
    { deep: true },
);

function onUploadImage(urls: string[]) {
    urls.forEach((r) => {
        // editor.commands.setImage({ src: 'https://example.com/foobar.png' })
        editor.value?.commands.setImage({ src: r });
    });
    editorStore.updateImageUploadModal(false);
}
</script>

<template>
    <editor-content v-bind="$attrs" :editor="editor" />

    <div>
        <Dialog
            :open="editorStore.isImageUploadModalOpen.value"
            @update:open="(v) => editorStore.updateImageUploadModal(v)"
        >
            <!-- <DialogTrigger> Edit Profile </DialogTrigger> -->
            <DialogContent
                class="max-h-[90dvh] grid-rows-[auto_minmax(0,1fr)_auto] px-0"
            >
                <DialogHeader class="p-6 pb-0">
                    <DialogTitle>Upload Image</DialogTitle>
                    <DialogDescription>
                        Upload image from your computer or from a link
                    </DialogDescription>
                </DialogHeader>
                <div class="overflow-y-auto px-6 py-4">
                    <UploadMediaForm :onUpload="onUploadImage" />
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style>
.tiptap p.is-editor-empty:first-child::before {
    color: hsl(var(--muted-foreground));
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>
