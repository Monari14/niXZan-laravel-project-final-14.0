<template>
  <div class="login-view">
    <h1>Entrar</h1>
    <form @submit.prevent="handleLogin">
      <input v-model="form.email" type="email" placeholder="Email ou username" required />
      <input v-model="form.password" type="password" placeholder="Senha" required />
      <button type="submit" :disabled="loading">
        {{ loading ? 'Entrando...' : 'Entrar' }}
      </button>
    </form>
    <router-link to="/register">Não tem conta? Cadastre-se</router-link>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/services/api'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  email: '',
  password: '',
})

async function handleLogin() {
  loading.value = true
  try {
    const { data } = await axios.post('/login', form)
    localStorage.setItem('token', data.token)
    router.push('/feed')
  } catch {
    alert('Falha no login')
  } finally {
    loading.value = false
  }
}
</script>
