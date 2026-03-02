<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-100 p-6">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
      <h1 class="text-2xl font-bold">Create Account</h1>
      <p class="mt-1 text-sm text-slate-500">Start your AI SaaS workspace</p>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.name" type="text" placeholder="Name" class="w-full rounded-xl border p-3" required />
        <input v-model="form.email" type="email" placeholder="Email" class="w-full rounded-xl border p-3" required />
        <input v-model="form.password" type="password" placeholder="Password" class="w-full rounded-xl border p-3" required />
        <input v-model="form.password_confirmation" type="password" placeholder="Confirm password" class="w-full rounded-xl border p-3" required />
        <button class="w-full rounded-xl bg-indigo-600 py-2.5 font-semibold text-white">Register</button>
      </form>

      <p v-if="message" class="mt-4 text-sm text-green-600">{{ message }}</p>
      <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>

      <p class="mt-4 text-sm">
        Have an account?
        <a href="/login" class="font-semibold text-indigo-600">Login</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const message = ref('');
const error = ref('');

async function submit() {
  error.value = '';
  message.value = '';

  try {
    const { data } = await axios.post('/api/v1/register', form);
    localStorage.setItem('api_token', data.token);
    message.value = 'Registration successful. Verification email sent.';
    window.location.href = '/dashboard';
  } catch (e) {
    error.value = e?.response?.data?.message || 'Registration failed.';
  }
}
</script>
