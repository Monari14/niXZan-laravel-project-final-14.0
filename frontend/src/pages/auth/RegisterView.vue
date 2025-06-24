<template>
  <div class="register-view">
    <h1>Criar Conta</h1>
    <form @submit.prevent="handleRegister">
      <input v-model="form.name" type="text" placeholder="Nome" required />
      <input v-model="form.username" type="text" placeholder="Username" required />
      <input v-model="form.email" type="email" placeholder="Email" required />
      <input v-model="form.password" type="password" placeholder="Senha" required />
      <input v-model="form.password_confirmation" type="password" placeholder="Confirmar Senha" required />
      <button type="submit" :disabled="loading">
        {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
      </button>
    </form>
    <router-link to="/login">Já tem conta? Faça login</router-link>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/services/api'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
})

async function handleRegister() {
  loading.value = true
  try {
    const { data } = await axios.post('/register', form)
    localStorage.setItem('token', data.token)
    router.push('/feed')
  } catch {
    alert('Falha no cadastro')
  } finally {
    loading.value = false
  }
}
</script>
