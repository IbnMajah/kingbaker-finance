# Session Management & CSRF Token Refresh

This document explains the new session management features implemented to prevent "page expired" errors in your Laravel + Inertia.js application.

## What Was Implemented

### 1. Extended Session Lifetime
- **Before**: 2 hours (120 minutes)
- **After**: 12 hours (720 minutes)
- **File**: `config/session.php` and `.env`

### 2. Session Keep-Alive System
- **Automatic keep-alive**: Every 5 minutes
- **Session warning**: 10 minutes before expiry
- **Activity tracking**: Mouse, keyboard, scroll events
- **File**: `resources/js/composables/useSessionKeepAlive.js`

### 3. CSRF Token Refresh
- **Automatic refresh**: Every 30 minutes
- **Form submission**: Pre-submission token validation
- **Token update**: Updates all forms and meta tags
- **File**: `resources/js/composables/useFormTokenRefresh.js`

### 4. API Endpoints
- **Keep-alive**: `GET /api/session/keep-alive`
- **Token refresh**: `POST /api/session/refresh`
- **File**: `app/Http/Controllers/SessionController.php`

## How to Use in Forms

### Option 1: Automatic (Recommended)
Simply import and use the composable in your form components:

**Example: ExpenseClaims Create Component**
```vue
<script>
import { useFormTokenRefresh } from '@/composables/useFormTokenRefresh.js';

export default {
  setup() {
    const { ensureValidToken } = useFormTokenRefresh();
    
    return {
      ensureValidToken
    };
  },
  methods: {
    async submit() {
      // This will automatically refresh the token if needed
      await this.ensureValidToken();
      
      this.form.post('/expense-claims');
    }
  }
}
</script>
```

**Example: ExpenseClaims Edit Component**
```vue
<script>
import { useFormTokenRefresh } from '@/composables/useFormTokenRefresh.js';

export default {
  setup() {
    const { ensureValidToken } = useFormTokenRefresh();
    
    return {
      ensureValidToken
    };
  },
  methods: {
    async submit() {
      await this.ensureValidToken();
      this.form.put(`/expense-claims/${this.expenseClaim.id}`);
    },
    async destroy() {
      if (confirm('Are you sure?')) {
        await this.ensureValidToken();
        this.$inertia.delete(`/expense-claims/${this.expenseClaim.id}`);
      }
    }
  }
}
</script>
```

```vue
<script>
import { useFormTokenRefresh } from '@/composables/useFormTokenRefresh.js'

export default {
  setup() {
    const { ensureValidToken } = useFormTokenRefresh()
    
    return {
      ensureValidToken
    }
  },
  methods: {
    async submitForm() {
      // This will automatically refresh the token if needed
      await this.ensureValidToken()
      
      // Then submit your form
      this.form.post('/your-endpoint')
    }
  }
}
</script>
```

### Option 2: Manual Refresh
For more control, you can manually refresh tokens:

```vue
<script>
import { useFormTokenRefresh } from '@/composables/useFormTokenRefresh.js'

export default {
  setup() {
    const { manualRefresh, isRefreshing } = useFormTokenRefresh()
    
    return {
      manualRefresh,
      isRefreshing
    }
  },
  methods: {
    async refreshToken() {
      if (await this.manualRefresh()) {
        console.log('Token refreshed successfully')
      }
    }
  }
}
</script>
```

## Configuration

### Session Lifetime
- **Environment Variable**: `SESSION_LIFETIME=720` (12 hours)
- **Config File**: `config/session.php`
- **Default**: 720 minutes (12 hours)

### Keep-Alive Intervals
- **Keep-alive requests**: Every 5 minutes
- **Session warning**: 10 minutes before expiry
- **Token refresh**: Every 30 minutes
- **Minimum refresh interval**: 5 minutes

## Files Modified/Created

### New Files
- `config/session.php` - Session configuration
- `app/Http/Controllers/SessionController.php` - Session API controller
- `routes/api.php` - API routes for session management
- `resources/js/composables/useSessionKeepAlive.js` - Session keep-alive logic
- `resources/js/composables/useFormTokenRefresh.js` - Form token refresh logic

### Modified Files
- `.env` - Updated SESSION_LIFETIME to 720
- `resources/views/app.blade.php` - Added CSRF meta tag
- `resources/js/app.js` - Integrated session keep-alive
- `resources/js/Pages/BankAccounts/Create.vue` - Example implementation
- `resources/js/Pages/ExpenseClaims/Create.vue` - Added token refresh
- `resources/js/Pages/ExpenseClaims/Edit.vue` - Added token refresh

## How It Works

### 1. Session Keep-Alive
- Automatically starts when user is authenticated
- Makes lightweight requests every 5 minutes
- Tracks user activity (mouse, keyboard, scroll)
- Shows warning 10 minutes before session expiry

### 2. CSRF Token Refresh
- Automatically refreshes tokens every 30 minutes
- Validates tokens before form submission
- Updates all hidden CSRF input fields
- Updates meta tag for JavaScript access

### 3. Form Protection
- Prevents "page expired" errors
- Automatically refreshes tokens when needed
- Seamless user experience
- No manual intervention required

## Benefits

1. **No More Page Expired Errors**: CSRF tokens are automatically refreshed
2. **Extended Sessions**: 12-hour sessions instead of 2-hour
3. **Better UX**: Users can work on forms for longer periods
4. **Automatic**: No manual token management required
5. **Secure**: Maintains CSRF protection while improving usability

## Troubleshooting

### Session Still Expiring
1. Check `.env` file for `SESSION_LIFETIME=720`
2. Clear config cache: `php artisan config:clear`
3. Verify session config is loaded: `php artisan config:show session`

### CSRF Token Issues
1. Ensure CSRF meta tag is present in layout
2. Check browser console for JavaScript errors
3. Verify API routes are accessible: `php artisan route:list --path=api`

### Keep-Alive Not Working
1. Check browser console for network errors
2. Verify user is authenticated
3. Check API endpoint: `GET /api/session/keep-alive`

## Security Considerations

- **Session migration**: Session IDs are regenerated for security
- **CSRF protection**: Maintained while improving usability
- **Rate limiting**: Prevents abuse of refresh endpoints
- **Activity tracking**: Only tracks legitimate user interaction

## Future Enhancements

- **Configurable intervals**: Make keep-alive intervals configurable
- **User notifications**: Better UI for session warnings
- **Analytics**: Track session usage patterns
- **Mobile optimization**: Better mobile session handling
