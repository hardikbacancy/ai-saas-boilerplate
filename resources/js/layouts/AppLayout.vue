<template>
  <div class="min-h-screen bg-slate-50">
    <header class="border-b bg-white">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        <div class="text-lg font-semibold">AI SaaS Boilerplate</div>
        <nav class="flex items-center gap-4 text-sm">
          <a class="hover:text-indigo-600" href="/dashboard">Dashboard</a>
          <a class="hover:text-indigo-600" href="/billing">Billing</a>
          <a class="hover:text-indigo-600" href="/security">Security</a>
          <button class="rounded bg-slate-900 px-3 py-1.5 text-white" @click="logout">Logout</button>
        </nav>
      </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import axios from 'axios';

async function logout() {
  const token = localStorage.getItem('api_token');
  if (token) {
    await axios.post('/api/v1/logout', {}, {
      headers: { Authorization: `Bearer ${token}` },
    });
  }

  localStorage.removeItem('api_token');
  window.location.href = '/login';
}
</script>
