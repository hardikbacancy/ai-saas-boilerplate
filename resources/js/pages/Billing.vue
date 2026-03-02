<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="rounded-2xl bg-white p-6 shadow">
        <h1 class="text-2xl font-bold">Subscription Billing</h1>
        <p class="mt-1 text-sm text-slate-500">Stripe sandbox integration for plan upgrades.</p>
      </div>

      <div class="grid gap-4 md:grid-cols-3">
        <div v-for="(plan, key) in plans" :key="key" class="rounded-2xl bg-white p-6 shadow">
          <p class="text-lg font-semibold">{{ plan.name }}</p>
          <p class="mt-1 text-sm text-slate-500">${{ plan.price }}/month</p>
          <ul class="mt-3 space-y-1 text-sm text-slate-600">
            <li v-for="feature in plan.features" :key="feature">- {{ feature }}</li>
          </ul>
          <button
            v-if="key !== 'free'"
            class="mt-4 rounded bg-indigo-600 px-4 py-2 text-white"
            @click="subscribe(key)"
          >
            Choose {{ plan.name }}
          </button>
        </div>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Actions</h2>
        <div class="mt-4 flex flex-wrap gap-3">
          <button class="rounded bg-rose-600 px-4 py-2 text-white" @click="cancel">Cancel Subscription</button>
          <button class="rounded bg-emerald-600 px-4 py-2 text-white" @click="resume">Resume Subscription</button>
        </div>
        <p v-if="message" class="mt-3 text-sm text-emerald-700">{{ message }}</p>
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

const plans = ref({});
const message = ref('');
const error = ref('');

onMounted(loadPlans);

async function loadPlans() {
  try {
    const token = localStorage.getItem('api_token');
    const { data } = await axios.get('/api/v1/billing/plans', {
      headers: { Authorization: `Bearer ${token}` },
    });
    plans.value = data.plans || {};
  } catch (_) {
    error.value = 'Unable to load plans.';
  }
}

async function subscribe(plan) {
  message.value = '';
  error.value = '';
  try {
    const token = localStorage.getItem('api_token');
    const { data } = await axios.post('/api/v1/billing/subscribe', {
      plan,
      payment_method_id: 'pm_card_visa',
    }, {
      headers: { Authorization: `Bearer ${token}` },
    });
    message.value = data.message;
  } catch (e) {
    error.value = e?.response?.data?.message || 'Subscribe failed.';
  }
}

async function cancel() {
  message.value = '';
  error.value = '';
  try {
    const token = localStorage.getItem('api_token');
    const { data } = await axios.post('/api/v1/billing/cancel', {}, {
      headers: { Authorization: `Bearer ${token}` },
    });
    message.value = data.message;
  } catch (e) {
    error.value = e?.response?.data?.message || 'Cancel failed.';
  }
}

async function resume() {
  message.value = '';
  error.value = '';
  try {
    const token = localStorage.getItem('api_token');
    const { data } = await axios.post('/api/v1/billing/resume', {}, {
      headers: { Authorization: `Bearer ${token}` },
    });
    message.value = data.message;
  } catch (e) {
    error.value = e?.response?.data?.message || 'Resume failed.';
  }
}
</script>
