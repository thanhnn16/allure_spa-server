<template>
    <div class="json-editor-container" :style="{ height: height || '300px' }">
        <div ref="editorElement" class="h-full"></div>
        <div v-if="error" class="text-red-500 text-sm mt-2">
            {{ error }}
        </div>
        <div class="flex justify-end mt-2 space-x-2">
            <button @click="formatJson" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded">
                Format
            </button>
            <button @click="validateJson" class="px-3 py-1 text-sm bg-blue-100 hover:bg-blue-200 rounded">
                Validate
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { EditorView, minimalSetup } from 'codemirror';
import { json } from '@codemirror/lang-json';
import { oneDark } from '@codemirror/theme-one-dark';

const props = defineProps({
    modelValue: {
        type: [String, Object],
        required: true
    },
    schema: {
        type: Object,
        default: null
    },
    height: {
        type: String,
        default: '300px'
    }
});

const emit = defineEmits(['update:modelValue', 'error']);
const error = ref('');
const editorElement = ref(null);
let editor = null;

onMounted(() => {
    const initialDoc = typeof props.modelValue === 'string'
        ? props.modelValue
        : JSON.stringify(props.modelValue, null, 2);

    editor = new EditorView({
        doc: initialDoc,
        extensions: [
            minimalSetup,
            json(),
            oneDark,
            EditorView.updateListener.of(update => {
                if (update.docChanged) {
                    const value = update.state.doc.toString();
                    updateValue(value);
                }
            })
        ],
        parent: editorElement.value
    });
});

watch(() => props.modelValue, (newVal) => {
    if (!editor) return;

    const newContent = typeof newVal === 'string'
        ? newVal
        : JSON.stringify(newVal, null, 2);

    if (newContent !== editor.state.doc.toString()) {
        editor.dispatch({
            changes: {
                from: 0,
                to: editor.state.doc.length,
                insert: newContent
            }
        });
    }
});

const formatJson = () => {
    try {
        const currentValue = editor.state.doc.toString();
        const parsed = JSON.parse(currentValue);
        const formatted = JSON.stringify(parsed, null, 2);

        editor.dispatch({
            changes: {
                from: 0,
                to: editor.state.doc.length,
                insert: formatted
            }
        });

        error.value = '';
    } catch (e) {
        error.value = 'Invalid JSON format';
    }
};

const validateJson = () => {
    try {
        const value = editor.state.doc.toString();
        const parsed = JSON.parse(value);
        error.value = '';
        emit('update:modelValue', parsed);
        return true;
    } catch (e) {
        error.value = e.message;
        emit('error', e.message);
        return false;
    }
};

const updateValue = (value) => {
    try {
        const newValue = typeof value === 'object' 
            ? JSON.stringify(value, null, 2) 
            : value;
            
        emit('update:modelValue', newValue);
        emit('error', null);
    } catch (e) {
        emit('error', e.message);
    }
};
</script>

<style>
.json-editor-container {
    @apply border border-gray-300 rounded-lg overflow-hidden;
    height: 300px;
}

.cm-editor {
    height: calc(100% - 40px);
    overflow: auto !important;
}

.cm-editor .cm-scroller {
    font-family: monospace;
    line-height: 1.4;
    overflow: auto !important;
    min-height: 100%;
}

.cm-editor .cm-content {
    padding: 10px;
    white-space: pre-wrap;
    word-break: break-word;
}

.cm-editor .cm-scroller::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.cm-editor .cm-scroller::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.cm-editor .cm-scroller::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.cm-editor .cm-scroller::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>