<template>
  <div>
    <Head title="Two-Factor Authentication" />
    <div class="flex items-center justify-center p-6 min-h-screen bg-gradient-to-br from-[#9B672A] to-[#9B672A]/80">
      <div class="w-full max-w-md">
        <logo class="block mx-auto w-full max-w-xs fill-white" height="50" />
        <form class="mt-8 bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-white/20" @submit.prevent="verify">
          <div class="px-10 py-12">
            <div class="flex justify-center mb-6">
              <div class="w-16 h-16 bg-[#9B672A]/10 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-[#9B672A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
            </div>
            <h1 class="text-center font-bold text-2xl text-[#9B672A]">Two-Factor Authentication</h1>
            <p class="text-center text-gray-600 mt-2">Enter the 6-digit code from your authenticator app to continue</p>
            <div class="mt-6 mx-auto w-24 border-b-2 border-[#9B672A]/20" />

            <div class="mt-8">
              <text-input
                v-model="form.code"
                :error="form.errors.code"
                label="Verification Code"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"
                placeholder="Enter 6-digit code"
                autocomplete="one-time-code"
                autofocus
              />
            </div>
          </div>
          <div class="px-10 py-6 bg-gray-50 border-t border-gray-100 space-y-3">
            <loading-button
              :loading="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-medium text-white bg-[#9B672A] hover:bg-[#9B672A]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9B672A] transition-all duration-200 ease-in-out transform hover:scale-[1.02]"
              type="submit"
            >
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Verify
              </span>
            </loading-button>
            <button
              type="button"
              class="w-full text-center text-sm text-gray-500 hover:text-gray-700"
              @click="logout"
            >
              Sign out and return to login
            </button>
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
    Logo,
    TextInput,
    LoadingButton,
  },
  data() {
    return {
      form: this.$inertia.form({
        code: '',
      }),
    }
  },
  methods: {
    verify() {
      this.form.post('/two-factor/verify')
    },
    logout() {
      this.$inertia.delete('/logout')
    },
  },
}
</script>
