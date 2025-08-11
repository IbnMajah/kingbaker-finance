import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'

export const usePermissionsStore = defineStore('permissions', {
    state: () => ({
        user: null,
        roles: [],
        permissions: [],
        loading: false,
    }),

    getters: {
        // Check if user has a specific permission
        hasPermission: (state) => (permission) => {
            if (!state.user) return false

            // Admin and owner always have all permissions
            if (state.user.role === 'admin' || state.user.owner) {
                return true
            }

            return state.permissions.includes(permission)
        },

        // Check if user has any of the given permissions
        hasAnyPermission: (state) => (permissions) => {
            if (!state.user) return false

            // Admin and owner always have all permissions
            if (state.user.role === 'admin' || state.user.owner) {
                return true
            }

            return permissions.some(permission => state.permissions.includes(permission))
        },

        // Check if user has all of the given permissions
        hasAllPermissions: (state) => (permissions) => {
            if (!state.user) return false

            // Admin and owner always have all permissions
            if (state.user.role === 'admin' || state.user.owner) {
                return true
            }

            return permissions.every(permission => state.permissions.includes(permission))
        },

        // Check if user has a specific role
        hasRole: (state) => (role) => {
            if (!state.user) return false
            return state.roles.includes(role)
        },

        // Check if user has any of the given roles
        hasAnyRole: (state) => (roles) => {
            if (!state.user) return false
            return roles.some(role => state.roles.includes(role))
        },

        // Module access getters
        canAccessFinance: (state) => {
            return state.hasPermission('view_finance')
        },

        canAccessSales: (state) => {
            return state.hasPermission('view_sales')
        },

        canAccessInventory: (state) => {
            return state.hasPermission('view_inventory')
        },

        canAccessHR: (state) => {
            return state.hasPermission('view_hr')
        },

        // Finance module permissions
        canCreateBankAccounts: (state) => {
            return state.hasPermission('create_bank_accounts')
        },

        canEditBankAccounts: (state) => {
            return state.hasPermission('edit_bank_accounts')
        },

        canCreateDeposits: (state) => {
            return state.hasPermission('create_deposits')
        },

        canEditDeposits: (state) => {
            return state.hasPermission('edit_deposits')
        },

        canCreateBills: (state) => {
            return state.hasPermission('create_bills')
        },

        canCreateChequePayments: (state) => {
            return state.hasPermission('create_cheque_payments')
        },

        canCreateExpenses: (state) => {
            return state.hasPermission('create_expenses')
        },

        canCreateInvoices: (state) => {
            return state.hasPermission('create_invoices')
        },

        canCreateVendors: (state) => {
            return state.hasPermission('create_vendors')
        },

        canViewBankAccounts: (state) => {
            return state.hasPermission('view_bank_accounts')
        },

        canViewBills: (state) => {
            return state.hasPermission('view_bills')
        },

        canEditBills: (state) => {
            return state.hasPermission('edit_bills')
        },

        canViewChequePayments: (state) => {
            return state.hasPermission('view_cheque_payments')
        },

        canEditChequePayments: (state) => {
            return state.hasPermission('edit_cheque_payments')
        },

        canViewDeposits: (state) => {
            return state.hasPermission('view_deposits')
        },

        canViewInvoices: (state) => {
            return state.hasPermission('view_invoices')
        },

        canEditInvoices: (state) => {
            return state.hasPermission('edit_invoices')
        },

        canViewVendors: (state) => {
            return state.hasPermission('view_vendors')
        },

        canEditVendors: (state) => {
            return state.hasPermission('edit_vendors')
        },

        canViewUsers: (state) => {
            return state.hasPermission('view_users')
        },

        canEditUsers: (state) => {
            return state.hasPermission('edit_users')
        },

        canViewSales: (state) => {
            return state.hasPermission('view_sales')
        },

        canEditSales: (state) => {
            return state.hasPermission('edit_sales')
        },

        canViewExpenses: (state) => {
            return state.hasPermission('view_expenses')
        },

        canEditExpenses: (state) => {
            return state.hasPermission('edit_expenses')
        },

        canViewBranches: (state) => {
            return state.hasPermission('view_branches')
        },

        canEditBranches: (state) => {
            return state.hasPermission('edit_branches')
        },

        canViewOrganizations: (state) => {
            return state.hasPermission('view_organizations')
        },

        canEditOrganizations: (state) => {
            return state.hasPermission('edit_organizations')
        },

        canViewTransactions: (state) => {
            return state.hasPermission('view_transactions')
        },

        canApproveExpenses: (state) => {
            return state.hasPermission('approve_expenses')
        },

        canReconcileTransactions: (state) => {
            return state.hasPermission('reconcile_transactions')
        },

        canApproveTransactions: (state) => {
            return state.hasPermission('approve_transactions')
        },

        canPrintReceipts: (state) => {
            return state.hasPermission('print_receipts')
        },

        canGenerateReports: (state) => {
            return state.hasPermission('generate_reports')
        },

        // Sales module permissions
        canCreateSales: (state) => {
            return state.hasPermission('create_sales')
        },

        canViewAllSales: (state) => {
            return state.hasPermission('view_all_sales')
        },

        canViewOwnSalesOnly: (state) => {
            return state.hasPermission('view_own_sales') && !state.hasPermission('view_all_sales')
        },

        // Inventory module permissions
        canCreateProducts: (state) => {
            return state.hasPermission('create_products')
        },

        canManageProductionLogs: (state) => {
            return state.hasPermission('manage_production_logs')
        },

        canAdjustStock: (state) => {
            return state.hasPermission('adjust_stock')
        },

        // System permissions
        canCreateUsers: (state) => {
            return state.hasPermission('create_users')
        },

        canAssignRoles: (state) => {
            return state.hasPermission('assign_roles')
        },

        canViewAuditLogs: (state) => {
            return state.hasPermission('view_audit_logs')
        },

        canExportData: (state) => {
            return state.hasPermission('export_data')
        },

        canCreateOrganizations: (state) => {
            return state.hasPermission('create_organizations')
        },

        canCreateBranches: (state) => {
            return state.hasPermission('create_branches')
        },

        // Branch access
        canViewAllBranches: (state) => {
            return state.hasPermission('view_all_branches')
        },

        canViewOwnBranchOnly: (state) => {
            return state.hasPermission('view_own_branch_only')
        },

        // Role-based getters
        isAdmin: (state) => {
            return state.user?.role === 'admin' || state.user?.owner || state.hasRole('admin')
        },

        isFinanceManager: (state) => {
            return state.hasRole('finance_manager')
        },

        isAccountant: (state) => {
            return state.hasRole('accountant')
        },

        isSalesStaff: (state) => {
            return state.hasRole('sales_staff')
        },

        isInventorySupervisor: (state) => {
            return state.hasRole('inventory_supervisor')
        },

        isProcurementStaff: (state) => {
            return state.hasRole('procurement_staff')
        },

        isAuditor: (state) => {
            return state.hasRole('auditor')
        },

        isBranchSupervisor: (state) => {
            return state.hasRole('branch_supervisor')
        },

        // Check if user can access a specific branch
        canAccessBranch: (state) => (branchId) => {
            if (!state.user) return false

            // Admin and owner can access all branches
            if (state.user.role === 'admin' || state.user.owner) {
                return true
            }

            // If user can view all branches
            if (state.hasPermission('view_all_branches')) {
                return true
            }

            // If user can only view own branch
            if (state.hasPermission('view_own_branch_only')) {
                return !branchId || state.user.branch_id === branchId
            }

            return false
        },
    },

    actions: {
        // Initialize user permissions from server-side data
        setUser(userData) {
            this.user = userData.user
            this.roles = userData.roles || []
            this.permissions = userData.permissions || []
        },

        // Clear user data on logout
        clearUser() {
            this.user = null
            this.roles = []
            this.permissions = []
        },

        // Fetch fresh user permissions from server
        async fetchUserPermissions() {
            this.loading = true
            try {
                router.get('/api/user-permissions', {}, {
                    onSuccess: (page) => {
                        this.setUser(page.props)
                    },
                    onError: () => {
                        console.error('Failed to fetch user permissions')
                    },
                    onFinish: () => {
                        this.loading = false
                    }
                })
            } catch (error) {
                console.error('Error fetching user permissions:', error)
                this.loading = false
            }
        },
    },
})
