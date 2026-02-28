<template>
  <div>
    <Head title="Set Up Two-Factor Authentication" />
    <div class="flex items-center justify-center p-6 min-h-screen bg-gradient-to-br from-[#9B672A] to-[#9B672A]/80">
      <div class="w-full max-w-md">
        <logo class="block mx-auto w-full max-w-xs fill-white" height="50" />
        <div class="mt-8 bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-white/20">
          <div class="px-10 py-12">
            <h1 class="text-center font-bold text-2xl text-[#9B672A]">Set Up Two-Factor Authentication</h1>
            <p class="text-center text-gray-600 mt-2">Scan the QR code below with your authenticator app (e.g. Google Authenticator, Authy)</p>
            <div class="mt-6 mx-auto w-24 border-b-2 border-[#9B672A]/20" />

            <div class="mt-8 flex justify-center">
              <img :src="qrCode" alt="QR Code" class="w-48 h-48" />
            </div>

            <div class="mt-6">
              <p class="text-sm text-gray-500 text-center">Or enter this key manually:</p>
              <div class="mt-2 flex items-center justify-center gap-2">
                <code class="bg-gray-100 px-3 py-2 rounded-lg text-sm font-mono tracking-wider text-gray-700 select-all">{{ secret }}</code>
              </div>
            </div>

            <form class="mt-8" @submit.prevent="confirm">
              <div>
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

              <div class="mt-6">
                <loading-button
                  :loading="form.processing"
                  class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-medium text-white bg-[#9B672A] hover:bg-[#9B672A]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9B672A] transition-all duration-200 ease-in-out transform hover:scale-[1.02]"
                  type="submit"
                >
                  <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Verify &amp; Enable 2FA
                  </span>
                </loading-button>
              </div>
            </form>
          </div>
          <div class="px-10 py-4 bg-gray-50 border-t border-gray-100">
            <button
              class="w-full text-center text-sm text-gray-500 hover:text-gray-700"
              @click="logout"
            >
              Sign out and return to login
            </button>
          </div>
        </div>
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
  props: {
    qrCode: String,
    secret: String,
  },
  data() {
    return {
      form: this.$inertia.form({
        code: '',
      }),
    }
  },
  methods: {
    confirm() {
      this.form.post('/two-factor/confirm')
    },
    logout() {
      this.$inertia.delete('/logout')
    },
  },
}
</script>
