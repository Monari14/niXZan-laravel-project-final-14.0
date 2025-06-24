<template>
  <div class="feed-view">
    <h1>Feed</h1>

    <div v-if="loading">Carregando...</div>

    <div v-else>
      <div v-if="posts.length === 0">Nenhuma postagem encontrada.</div>

      <div v-for="post in posts" :key="post.id" class="post-card">
        <div class="post-header">
          <img :src="post.user.avatar" alt="Avatar" class="avatar" />
          <strong>{{ post.user.username }}</strong>
        </div>
        <h3>{{ post.title }}</h3>
        <p>{{ post.content }}</p>
        <small>{{ post.likes_count }} 👍</small>
      </div>

      <button v-if="pagination.current_page < pagination.last_page" @click="loadMore" :disabled="loadingMore">
        {{ loadingMore ? 'Carregando...' : 'Carregar mais' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from '@/services/api'

interface User {
  id: number
  username: string
  avatar: string
}

interface Post {
  id: number
  title: string
  content: string
  user: User
  likes_count: number
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
  // outros campos do Laravel pagination que você quiser
}

const posts = ref<Post[]>([])
const pagination = ref<Pagination>({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const loading = ref(false)
const loadingMore = ref(false)

async function fetchFeed(page = 1) {
  if (page === 1) loading.value = true
  else loadingMore.value = true

  try {
    const { data } = await axios.get('/posts/feed', {
      params: { page }
    })

    if (page === 1) {
      posts.value = data.data
    } else {
      posts.value.push(...data.data)
    }

    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
    }
  } catch {
    alert('Erro ao carregar o feed')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

function loadMore() {
  if (pagination.value.current_page < pagination.value.last_page) {
    fetchFeed(pagination.value.current_page + 1)
  }
}

onMounted(() => {
  fetchFeed()
})
</script>

<style scoped>
.feed-view {
  max-width: 600px;
  margin: auto;
}

.post-card {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 6px;
}

.post-header {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 0.5rem;
}
</style>
