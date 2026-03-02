<template>
  <div class="bg-white rounded-2xl shadow-lg p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800">🤖 AI Generator</h2>
      <div class="text-sm text-gray-500">
        <span class="font-semibold text-indigo-600">{{ usage.tokens_used.toLocaleString() }}</span>
        / {{ usage.tokens_limit === -1 ? '∞' : usage.tokens_limit.toLocaleString() }} tokens
      </div>
    </div>

    <!-- Token Usage Bar -->
    <div class="w-full bg-gray-200 rounded-full h-2 mb-6" v-if="usage.tokens_limit !== -1">
      <div
        class="h-2 rounded-full transition-all duration-500"
        :class="usagePercent > 80 ? 'bg-red-500' : 'bg-indigo-500'"
        :style="{ width: usagePercent + '%' }"
      />
    </div>

    <!-- Prompt Input -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Your Prompt</label>
      <textarea
        v-model="prompt"
        rows="4"
        placeholder="Describe what you want AI to generate..."
        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
      />
    </div>

    <!-- Settings Row -->
    <div class="flex items-center gap-4 mb-6">
      <div class="flex-1">
        <label class="block text-xs text-gray-500 mb-1">Max Tokens</label>
        <select v-model="maxTokens" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
          <option value="200">200 (Short)</option>
          <option value="500">500 (Medium)</option>
          <option value="1000">1000 (Long)</option>
        </select>
      </div>
      <button
        @click="generate"
        :disabled="loading || !prompt.trim()"
        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold py-2 px-6 rounded-xl transition"
      >
        <span v-if="loading" class="flex items-center justify-center gap-2">
          <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
          Generating...
        </span>
        <span v-else>⚡ Generate</span>
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-4 text-sm">
      {{ error }}
      <a v-if="upgradeNeeded" href="/billing" class="underline font-semibold ml-2">Upgrade Plan →</a>
    </div>

    <!-- Result -->
    <div v-if="result" class="bg-gray-50 rounded-xl p-4 border border-gray-200">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-gray-500">Generated Result</span>
        <button @click="copyResult" class="text-xs text-indigo-600 hover:underline">
          {{ copied ? '✅ Copied!' : '📋 Copy' }}
        </button>
      </div>
      <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ result }}</p>
      <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-400">
        Tokens used: {{ lastTokensUsed }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

// ─── State ───────────────────────────────────────────────────────────
const prompt        = ref('')
const maxTokens     = ref(500)
const loading       = ref(false)
const result        = ref('')
const error         = ref('')
const copied        = ref(false)
const upgradeNeeded = ref(false)
const lastTokensUsed = ref(0)
const usage = ref({ tokens_used: 0, tokens_limit: 100000 })

// ─── Computed ─────────────────────────────────────────────────────────
const usagePercent = computed(() => {
  if (usage.value.tokens_limit === -1) return 0
  return Math.min((usage.value.tokens_used / usage.value.tokens_limit) * 100, 100)
})

// ─── Methods ──────────────────────────────────────────────────────────
async function generate() {
  if (!prompt.value.trim()) return

  loading.value      = true
  error.value        = ''
  result.value       = ''
  upgradeNeeded.value = false

  try {
    const { data } = await axios.post('/api/v1/ai/generate', {
      prompt:     prompt.value,
      max_tokens: maxTokens.value,
    })

    result.value        = data.text
    lastTokensUsed.value = data.tokens_used
    usage.value.tokens_used = data.monthly_usage

  } catch (err) {
    if (err.response?.status === 402) {
      error.value        = err.response.data.error
      upgradeNeeded.value = true
    } else {
      error.value = err.response?.data?.error || 'Something went wrong.'
    }
  } finally {
    loading.value = false
  }
}

async function copyResult() {
  await navigator.clipboard.writeText(result.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

// Load usage on mount
async function loadUsage() {
  const { data } = await axios.get('/api/v1/ai/usage')
  usage.value = data
}

loadUsage()
</script>