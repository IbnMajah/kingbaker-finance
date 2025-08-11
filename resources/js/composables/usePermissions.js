import { computed } from 'vue'
import { usePermissionsStore } from '../stores/permissions.js'

export function usePermissions() {
    const store = usePermissionsStore()

    // Basic permission checks
    const hasPermission = (permission) => store.hasPermission(permission)
    const hasAnyPermission = (permissions) => store.hasAnyPermission(permissions)
    const hasAllPermissions = (permissions) => store.hasAllPermissions(permissions)
    const hasRole = (role) => store.hasRole(role)
    const hasAnyRole = (roles) => store.hasAnyRole(roles)



    // Module access
    const canAccessFinance = computed(() => store.canAccessFinance)
    const canAccessSales = computed(() => store.canAccessSales)
    const canAccessInventory = computed(() => store.canAccessInventory)
    const canAccessHR = computed(() => store.canAccessHR)

    // Finance permissions
    const canCreateBankAccounts = computed(() => store.canCreateBankAccounts)
    const canEditBankAccounts = computed(() => store.canEditBankAccounts)
    const canCreateDeposits = computed(() => store.canCreateDeposits)
    const canEditDeposits = computed(() => store.canEditDeposits)
    const canCreateBills = computed(() => store.canCreateBills)
    const canCreateChequePayments = computed(() => store.canCreateChequePayments)
    const canCreateExpenses = computed(() => store.canCreateExpenses)
    const canCreateInvoices = computed(() => store.canCreateInvoices)
    const canCreateVendors = computed(() => store.canCreateVendors)
    const canViewBankAccounts = computed(() => store.canViewBankAccounts)
    const canViewBills = computed(() => store.canViewBills)
    const canEditBills = computed(() => store.canEditBills)
    const canViewChequePayments = computed(() => store.canViewChequePayments)
    const canEditChequePayments = computed(() => store.canEditChequePayments)
    const canViewDeposits = computed(() => store.canViewDeposits)
    const canViewInvoices = computed(() => store.canViewInvoices)
    const canEditInvoices = computed(() => store.canEditInvoices)
    const canViewVendors = computed(() => store.canViewVendors)
    const canEditVendors = computed(() => store.canEditVendors)
    const canViewUsers = computed(() => store.canViewUsers)
    const canEditUsers = computed(() => store.canEditUsers)
    const canViewSales = computed(() => store.canViewSales)
    const canEditSales = computed(() => store.canEditSales)
    const canViewExpenses = computed(() => store.canViewExpenses)
    const canEditExpenses = computed(() => store.canEditExpenses)
    const canViewBranches = computed(() => store.canViewBranches)
    const canEditBranches = computed(() => store.canEditBranches)
    const canViewOrganizations = computed(() => store.canViewOrganizations)
    const canEditOrganizations = computed(() => store.canEditOrganizations)
    const canViewTransactions = computed(() => store.canViewTransactions)
    const canApproveExpenses = computed(() => store.canApproveExpenses)
    const canReconcileTransactions = computed(() => store.canReconcileTransactions)
    const canApproveTransactions = computed(() => store.canApproveTransactions)
    const canPrintReceipts = computed(() => store.canPrintReceipts)
    const canGenerateReports = computed(() => store.canGenerateReports)

    // Sales permissions
    const canCreateSales = computed(() => store.canCreateSales)
    const canViewAllSales = computed(() => store.canViewAllSales)
    const canViewOwnSalesOnly = computed(() => store.canViewOwnSalesOnly)

    // Inventory permissions
    const canCreateProducts = computed(() => store.canCreateProducts)
    const canManageProductionLogs = computed(() => store.canManageProductionLogs)
    const canAdjustStock = computed(() => store.canAdjustStock)

    // System permissions
    const canCreateUsers = computed(() => store.canCreateUsers)
    const canAssignRoles = computed(() => store.canAssignRoles)
    const canViewAuditLogs = computed(() => store.canViewAuditLogs)
    const canExportData = computed(() => store.canExportData)
    const canCreateOrganizations = computed(() => store.canCreateOrganizations)
    const canCreateBranches = computed(() => store.canCreateBranches)

    // Branch access
    const canViewAllBranches = computed(() => store.canViewAllBranches)
    const canViewOwnBranchOnly = computed(() => store.canViewOwnBranchOnly)
    const canAccessBranch = (branchId) => store.canAccessBranch(branchId)

    // Role checks
    const isAdmin = computed(() => store.isAdmin)
    const isFinanceManager = computed(() => store.isFinanceManager)
    const isAccountant = computed(() => store.isAccountant)
    const isSalesStaff = computed(() => store.isSalesStaff)
    const isInventorySupervisor = computed(() => store.isInventorySupervisor)
    const isProcurementStaff = computed(() => store.isProcurementStaff)
    const isAuditor = computed(() => store.isAuditor)
    const isBranchSupervisor = computed(() => store.isBranchSupervisor)

    // User info
    const user = computed(() => store.user)
    const userRoles = computed(() => store.roles)
    const userPermissions = computed(() => store.permissions)

    return {
        // Basic checks
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasAnyRole,



        // Module access
        canAccessFinance,
        canAccessSales,
        canAccessInventory,
        canAccessHR,

        // Finance permissions
        canCreateBankAccounts,
        canEditBankAccounts,
        canCreateDeposits,
        canEditDeposits,
        canCreateBills,
        canCreateChequePayments,
        canCreateExpenses,
        canCreateInvoices,
        canCreateVendors,
        canViewBankAccounts,
        canViewBills,
        canEditBills,
        canViewChequePayments,
        canEditChequePayments,
        canViewDeposits,
        canViewInvoices,
        canEditInvoices,
        canViewVendors,
        canEditVendors,
        canViewUsers,
        canEditUsers,
        canViewSales,
        canEditSales,
        canViewExpenses,
        canEditExpenses,
        canViewBranches,
        canEditBranches,
        canViewOrganizations,
        canEditOrganizations,
        canViewTransactions,
        canApproveExpenses,
        canReconcileTransactions,
        canApproveTransactions,
        canPrintReceipts,
        canGenerateReports,

        // Sales permissions
        canCreateSales,
        canViewAllSales,
        canViewOwnSalesOnly,

        // Inventory permissions
        canCreateProducts,
        canManageProductionLogs,
        canAdjustStock,

        // System permissions
        canCreateUsers,
        canAssignRoles,
        canViewAuditLogs,
        canExportData,
        canCreateOrganizations,
        canCreateBranches,

        // Branch access
        canViewAllBranches,
        canViewOwnBranchOnly,
        canAccessBranch,

        // Role checks
        isAdmin,
        isFinanceManager,
        isAccountant,
        isSalesStaff,
        isInventorySupervisor,
        isProcurementStaff,
        isAuditor,
        isBranchSupervisor,

        // User info
        user,
        userRoles,
        userPermissions,
    }
}
