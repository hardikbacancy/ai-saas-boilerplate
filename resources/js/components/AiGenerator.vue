<template>
  <div class="rounded-2xl bg-white p-6 shadow">
    <h2 class="mb-4 text-xl font-bold">AI Text Generator</h2>
    <textarea
      v-model="prompt"
      rows="6"
      class="w-full rounded-xl border p-3"
      placeholder="Ask the AI to write content..."
    />
    <div class="mt-3 flex items-center gap-3">
      <button :disabled="loading" class="rounded bg-indigo-600 px-4 py-2 text-white" @click="generate">
        {{ loading ? 'Generating...' : 'Generate' }}
      </button>
      <p class="text-sm text-slate-500" v-if="usage">Monthly usage: {{ usage.toLocaleString() }} tokens</p>
    </div>
    <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
    <div v-if="result" class="mt-4 rounded-xl bg-slate-100 p-4 text-sm whitespace-pre-wrap">{{ result }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const prompt = ref('');
const loading = ref(false);
const result = ref('');
const usage = ref(0);
const error = ref('');

async function generate() {
  error.value = '';
  result.value = '';
  loading.value = true;

  try {
    const token = localStorage.getItem('api_token');
    const { data } = await axios.post('/api/v1/ai/generate', {
      prompt: prompt.value,
      max_tokens: 600,
    }, {
      headers: { Authorization: `Bearer ${token}` },
    });

    result.value = data.text;
    usage.value = data.monthly_usage;
  } catch (e) {
    error.value = e?.response?.data?.error || 'Unable to generate AI response.';
  } finally {
    loading.value = false;
  }
}
</script>
