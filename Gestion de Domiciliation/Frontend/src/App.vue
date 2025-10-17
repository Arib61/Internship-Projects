<template>
  <div id="app">
    <div class="main-wrapper">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

function scheduleAutoLogout() {
  const expiresAt = localStorage.getItem("expires_at"); // Must be stored after login

  if (!expiresAt) return;

  const expirationTime = new Date(expiresAt).getTime();
  const now = Date.now();
  const timeout = expirationTime - now;

  if (timeout > 0) {
    setTimeout(async () => {
      // Call backend logout
      try {
        await axios.post('/logout', {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          }
        });
      } catch (e) {
        console.warn('Token already expired or logout failed silently.');
      }

      // Clear local storage and redirect
      localStorage.clear();
      router.push('/login');
    }, timeout);
  } else {
    // Already expired
    localStorage.clear();
    router.push('/login');
  }
}

onMounted(() => {
  scheduleAutoLogout();
});
</script>


