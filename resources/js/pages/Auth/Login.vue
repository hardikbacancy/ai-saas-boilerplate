<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-100 p-6">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
      <h1 class="text-2xl font-bold">Login</h1>
      <p class="mt-1 text-sm text-slate-500">Access your workspace</p>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.email" type="email" placeholder="Email" class="w-full rounded-xl border p-3" required />
        <input v-model="form.password" type="password" placeholder="Password" class="w-full rounded-xl border p-3" required />
        <input v-if="show2fa" v-model="form.two_factor_code" type="text" placeholder="2FA Code" class="w-full rounded-xl border p-3" />
        <button class="w-full rounded-xl bg-indigo-600 py-2.5 font-semibold text-white">Login</button>
      </form>

      <p v-if="message" class="mt-4 text-sm text-amber-600">{{ message }}</p>
      <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>

      <p class="mt-4 text-sm">
        No account?
        <a href="/register" class="font-semibold text-indigo-600">Register</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';

const form = reactive({
  email: '',
  password: '',
  two_factor_code: '',
});

const show2fa = ref(false);
const message = ref('');
const error = ref('');

async function submit() {
  error.value = '';
  message.value = '';

  try {
    const { data } = await axios.post('/api/v1/login', form);
    localStorage.setItem('api_token', data.token);
    window.location.href = '/dashboard';
  } catch (e) {
    const status = e?.response?.status;
    const payload = e?.response?.data || {};

    if (status === 202 || payload.requires_2fa) {
      show2fa.value = true;
      message.value = payload.message || '2FA code sent to your email.';
      return;
    }

    error.value = payload.error || payload.message || 'Login failed.';
  }
}
</script>
