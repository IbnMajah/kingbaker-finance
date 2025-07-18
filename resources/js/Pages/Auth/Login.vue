<template>
  <div>
    <Head title="Login" />
    <div class="flex items-center justify-center p-6 min-h-screen bg-gradient-to-br from-[#9B672A] to-[#9B672A]/80">
      <div class="w-full max-w-md">
        <logo class="block mx-auto w-full max-w-xs fill-white" height="50" />
        <form class="mt-8 bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-white/20" @submit.prevent="login">
          <div class="px-10 py-12">
            <h1 class="text-center font-bold text-3xl text-[#9B672A]">Welcome Back!</h1>
            <p class="text-center text-gray-600 mt-2">Please sign in to continue</p>
            <div class="mt-6 mx-auto w-24 border-b-2 border-[#9B672A]/20" />

            <div class="space-y-6 mt-10">
              <div class="relative">
                <text-input
                  v-model="form.email"
                  :error="form.errors.email"
                  label="Email"
                  type="email"
                  autofocus
                  autocapitalize="off"
                >
                  <template #prefix>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                  </template>
                </text-input>
              </div>

              <div class="relative">
                <text-input
                  v-model="form.password"
                  :error="form.errors.password"
                  :type="showPassword ? 'text' : 'password'"
                  label="Password"
                >
                  <template #prefix>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </template>
                </text-input>
                <button
                  type="button"
                  class="absolute right-3 top-[38px] text-gray-400 hover:text-gray-600"
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between mt-6">
              <label class="flex items-center select-none" for="remember">
                <input
                  id="remember"
                  v-model="form.remember"
                  class="w-4 h-4 mr-2 accent-[#9B672A] rounded border-gray-300 focus:ring-[#9B672A]"
                  type="checkbox"
                />
                <span class="text-sm text-gray-700">Remember Me</span>
              </label>
            </div>
          </div>
          <div class="px-10 py-6 bg-gray-50 border-t border-gray-100">
            <loading-button
              :loading="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-medium text-white bg-[#9B672A] hover:bg-[#9B672A]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9B672A] transition-all duration-200 ease-in-out transform hover:scale-[1.02]"
              type="submit"
            >
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
              </span>
            </loading-button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Logo from '@/Shared/Logo.vue'
import TextInput from '@/Shared/TextInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'

export default {
  components: {
    Head,
    LoadingButton,
    Logo,
    TextInput,
  },
  data() {
    return {
      form: this.$inertia.form({
        email: '',
        password: '',
        remember: false,
      }),
      showPassword: false,
    }
  },
  methods: {
    login() {
      this.form.post('/login')
    },
  },
}
</script>
