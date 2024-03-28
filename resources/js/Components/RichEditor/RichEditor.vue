<script lang="ts" setup>
import Link from "@tiptap/extension-link";
import { Placeholder } from "@tiptap/extension-placeholder";
import StarterKit from "@tiptap/starter-kit";
import { EditorContent, useEditor, type Content } from "@tiptap/vue-3";
import { watch } from "vue";
import {
    EditorCommandMenu,
    editorSuggestions,
} from "./slash-menu/editor-suggestions";

const props = withDefaults(
    defineProps<{
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
</script>

<template>
    <editor-content :editor="editor" />
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
