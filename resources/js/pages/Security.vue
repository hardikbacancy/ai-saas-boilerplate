<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="rounded-2xl bg-white p-6 shadow">
        <h1 class="text-2xl font-bold">Security</h1>
        <p class="mt-1 text-sm text-slate-500">Email verification and 2FA controls.</p>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Email Verification</h2>
        <button class="mt-3 rounded bg-indigo-600 px-4 py-2 text-white" @click="sendVerification">
          Send Verification Email
        </button>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Two-Factor Authentication</h2>
        <div class="mt-3 flex gap-3">
          <button class="rounded bg-emerald-600 px-4 py-2 text-white" @click="enable2fa">Enable 2FA</button>
          <button class="rounded bg-rose-600 px-4 py-2 text-white" @click="disable2fa">Disable 2FA</button>
        </div>
      </div>

      <p v-if="message" class="text-sm text-emerald-700">{{ message }}</p>
      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

const message = ref('');
const error = ref('');

function authHeader() {
  return { Authorization: `Bearer ${localStorage.getItem('api_token')}` };
}

async function sendVerification() {
  message.value = '';
  error.value = '';
  try {
    const { data } = await axios.post('/api/v1/email/verification-notification', {}, {
      headers: authHeader(),
    });
    message.value = data.message;
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to send verification email.';
  }
}

async function enable2fa() {
  message.value = '';
  error.value = '';
  try {
    const { data } = await axios.post('/api/v1/2fa/enable', {}, { headers: authHeader() });
    message.value = data.message;
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to enable 2FA.';
  }
}

async function disable2fa() {
  message.value = '';
  error.value = '';
  try {
    const { data } = await axios.post('/api/v1/2fa/disable', {}, { headers: authHeader() });
    message.value = data.message;
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to disable 2FA.';
  }
}
</script>
