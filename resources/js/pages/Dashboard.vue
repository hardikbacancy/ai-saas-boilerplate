<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="rounded-2xl bg-white p-6 shadow">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="mt-1 text-slate-500">
          Workspace:
          <span class="font-semibold">{{ team?.name || 'N/A' }}</span>
          · Plan:
          <span class="font-semibold capitalize">{{ team?.plan || 'free' }}</span>
        </p>
        <p class="mt-2 text-sm" :class="verified ? 'text-green-600' : 'text-amber-600'">
          {{ verified ? 'Email verified' : 'Email not verified' }}
        </p>
      </div>

      <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow">
          <p class="text-sm text-slate-500">Team Members</p>
          <p class="mt-2 text-3xl font-bold">{{ membersCount }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
          <p class="text-sm text-slate-500">Current Plan</p>
          <p class="mt-2 text-3xl font-bold capitalize">{{ team?.plan || 'free' }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
          <p class="text-sm text-slate-500">Role</p>
          <p class="mt-2 text-3xl font-bold capitalize">{{ primaryRole }}</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <AiGenerator />

        <div class="rounded-2xl bg-white p-6 shadow">
          <h2 class="mb-4 text-xl font-bold">Invite Team Member</h2>
          <div class="space-y-3">
            <input v-model="invite.email" type="email" class="w-full rounded-xl border p-3" placeholder="member@email.com" />
            <select v-model="invite.role" class="w-full rounded-xl border p-3">
              <option value="member">Member</option>
              <option value="admin">Admin</option>
            </select>
            <button class="rounded bg-emerald-600 px-4 py-2 text-white" @click="sendInvite">Send Invitation</button>
            <p v-if="inviteMessage" class="text-sm text-emerald-700">{{ inviteMessage }}</p>
            <p v-if="inviteError" class="text-sm text-red-600">{{ inviteError }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import AiGenerator from '@/components/AiGenerator.vue';

const user = ref(null);
const team = ref(null);
const verified = ref(false);
const invite = reactive({ email: '', role: 'member' });
const inviteMessage = ref('');
const inviteError = ref('');

const membersCount = computed(() => team.value?.members?.length || 1);
const primaryRole = computed(() => user.value?.roles?.[0]?.name || 'member');

onMounted(loadMe);

async function loadMe() {
  const token = localStorage.getItem('api_token');
  if (!token) {
    window.location.href = '/login';
    return;
  }

  try {
    const { data } = await axios.get('/api/v1/me', {
      headers: { Authorization: `Bearer ${token}` },
    });
    user.value = data;
    team.value = data.current_team;
    verified.value = !!data.email_verified_at;
  } catch (_) {
    localStorage.removeItem('api_token');
    window.location.href = '/login';
  }
}

async function sendInvite() {
  inviteMessage.value = '';
  inviteError.value = '';

  try {
    const token = localStorage.getItem('api_token');
    const { data } = await axios.post('/api/v1/team/invite', invite, {
      headers: { Authorization: `Bearer ${token}` },
    });
    inviteMessage.value = data.message;
    invite.email = '';
  } catch (e) {
    inviteError.value = e?.response?.data?.error || e?.response?.data?.message || 'Failed to invite member.';
  }
}
</script>