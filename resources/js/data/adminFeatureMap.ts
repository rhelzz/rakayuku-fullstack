export type BackendReadiness = 'ready' | 'ui-preview';

export type AdminModuleKey =
    | 'auth'
    | 'material-flow'
    | 'projects'
    | 'inventory'
    | 'procurement'
    | 'sales'
    | 'finance'
    | 'hr'
    | 'debts';

export type AdminModule = {
    key: AdminModuleKey;
    title: string;
    routePath: string;
    readiness: BackendReadiness;
    summary: string;
    apiCoverage: string;
    controllers: string[];
    tableColumns: string[];
    flowSteps: string[];
};

export const adminModules: AdminModule[] = [
    {
        key: 'auth',
        title: 'Auth & Akses Pengguna',
        routePath: '/login',
        readiness: 'ready',
        summary: 'Sudah memiliki login, me, logout, dan logout-all dengan Sanctum token.',
        apiCoverage: '4 endpoint aktif',
        controllers: [
            'Api\\Auth\\AuthenticatedSessionController@store|destroy|destroyAll',
            'Api\\Auth\\CurrentUserController@__invoke',
        ],
        tableColumns: ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
        flowSteps: [
            'Login request divalidasi oleh LoginRequest.',
            'AuthenticateUserAction memverifikasi email dan password.',
            'IssueApiTokenAction membuat token sesuai role user.',
            'AuthUserResource mengembalikan data user terstandarisasi.',
        ],
    },
    {
        key: 'material-flow',
        title: 'Material Flow',
        routePath: '/admin/material-flow',
        readiness: 'ready',
        summary: 'Sudah memiliki endpoint proses material end-to-end termasuk auto purchase.',
        apiCoverage: '1 endpoint aktif (proses komprehensif)',
        controllers: ['Api\\Material\\MaterialFlowController@store'],
        tableColumns: [
            'required_qty',
            'stock_before',
            'shortage_qty',
            'purchased_qty',
            'stock_after',
            'gross_margin_percent',
        ],
        flowSteps: [
            'Resolve item: pilih item lama atau buat item baru.',
            'Upsert project_bom sesuai kebutuhan material.',
            'Cek stok dan tentukan apakah perlu auto purchase.',
            'Catat transaksi stok masuk/keluar dan update harga average.',
            'Perbarui project_cost_summary agar HPP dan margin ikut bergerak.',
        ],
    },
    {
        key: 'projects',
        title: 'Project',
        routePath: '/admin/projects',
        readiness: 'ui-preview',
        summary: 'Model dan skema project lengkap, endpoint list/CRUD belum tersedia.',
        apiCoverage: 'Belum ada endpoint project',
        controllers: [],
        tableColumns: [
            'code',
            'name',
            'customer_name',
            'selling_price',
            'actual_hpp',
            'gross_profit',
            'status',
            'payment_status',
        ],
        flowSteps: [
            'Project menjadi parent untuk BOM, invoice, work log, dan cost summary.',
            'Status proyek dan payment status dipakai untuk monitoring eksekusi dan tagihan.',
        ],
    },
    {
        key: 'inventory',
        title: 'Inventory',
        routePath: '/admin/inventory',
        readiness: 'ui-preview',
        summary: 'Model item dan kartu stok tersedia; endpoint list/CRUD belum tersedia.',
        apiCoverage: 'Belum ada endpoint inventory list',
        controllers: [],
        tableColumns: [
            'code',
            'name',
            'unit',
            'current_stock',
            'minimum_stock',
            'base_price',
            'transaction_type',
            'balance_after',
        ],
        flowSteps: [
            'Item menyimpan stok aktual, minimum stock, dan base price moving average.',
            'InventoryTransaction menyimpan mutasi in/out/return/adjustment per dokumen.',
        ],
    },
    {
        key: 'procurement',
        title: 'Procurement',
        routePath: '/admin/procurement',
        readiness: 'ui-preview',
        summary: 'PO master dan detail item sudah ada, saat ini dibuat otomatis dari material flow.',
        apiCoverage: 'Belum ada endpoint PO list/CRUD',
        controllers: [],
        tableColumns: [
            'po_number',
            'supplier_name',
            'project_id',
            'status',
            'total_amount',
            'po_date',
            'expected_date',
            'created_by',
        ],
        flowSteps: [
            'PO dibuat ketika shortage terjadi dan auto_purchase aktif.',
            'PurchaseOrderItem menyimpan qty_ordered, qty_received, unit_price, total_price.',
        ],
    },
    {
        key: 'sales',
        title: 'Sales & Invoice',
        routePath: '/admin/sales',
        readiness: 'ui-preview',
        summary: 'Model invoice, invoice item, dan payment lengkap; endpoint belum tersedia.',
        apiCoverage: 'Belum ada endpoint invoice list/CRUD',
        controllers: [],
        tableColumns: [
            'invoice_number',
            'project_id',
            'invoice_type',
            'status',
            'total_amount',
            'paid_amount',
            'remaining_amount',
            'due_date',
        ],
        flowSteps: [
            'Invoice menyimpan nilai tagihan per project.',
            'InvoicePayment mencatat metode bayar dan wallet penerimaan.',
        ],
    },
    {
        key: 'finance',
        title: 'Finance & Asset',
        routePath: '/admin/finance',
        readiness: 'ui-preview',
        summary: 'COA, financial transaction, dan asset tersedia; endpoint read/write belum tersedia.',
        apiCoverage: 'Belum ada endpoint finance list/CRUD',
        controllers: [],
        tableColumns: [
            'coa_code',
            'wallet_type',
            'transaction_type',
            'amount',
            'project_id',
            'document_ref',
            'transaction_date',
            'created_by',
        ],
        flowSteps: [
            'ChartOfAccount menjadi master kategori pencatatan keuangan.',
            'FinancialTransaction mengaitkan income/expense ke project, PO, atau invoice payment.',
            'Asset menyimpan harga perolehan, akumulasi penyusutan, dan nilai buku.',
        ],
    },
    {
        key: 'hr',
        title: 'HR & Workforce',
        routePath: '/admin/hr',
        readiness: 'ui-preview',
        summary: 'Employee, attendance, work log, dan kasbon sudah ada di model/database.',
        apiCoverage: 'Belum ada endpoint HR list/CRUD',
        controllers: [],
        tableColumns: [
            'employee_name',
            'daily_salary',
            'kasbon_balance',
            'leave_quota',
            'attendance_status',
            'work_type',
            'amount',
            'date',
        ],
        flowSteps: [
            'Attendance menyimpan status hadir/izin/sakit/cuti/alfa.',
            'WorkLog menangkap biaya tenaga kerja per project.',
            'KasbonTransaction menjaga histori kredit/debit kasbon karyawan.',
        ],
    },
    {
        key: 'debts',
        title: 'Debt & Receivable',
        routePath: '/admin/debts-receivables',
        readiness: 'ui-preview',
        summary: 'Model debts_receivables tersedia untuk hutang supplier dan piutang klien.',
        apiCoverage: 'Belum ada endpoint debt/receivable list/CRUD',
        controllers: [],
        tableColumns: [
            'type',
            'entity_name',
            'project_id',
            'invoice_id',
            'purchase_order_id',
            'total_amount',
            'paid_amount',
            'status',
            'due_date',
        ],
        flowSteps: [
            'Type debt untuk kewajiban ke supplier.',
            'Type receivable untuk piutang dari customer.',
            'Status unpaid/partial/paid dipakai untuk aging dan kolektibilitas.',
        ],
    },
];

export const featureFlowCards = [
    {
        title: 'Login API Flow',
        steps: [
            'POST /api/v1/auth/login',
            'LoginRequest validation',
            'AuthenticateUserAction',
            'IssueApiTokenAction',
            'AuthUserResource response',
        ],
    },
    {
        title: 'Material Flow API',
        steps: [
            'POST /api/v1/material-flow/process',
            'ProcessMaterialFlowRequest validation',
            'Resolve item and stock check',
            'Auto-purchase when shortage',
            'Update inventory, BOM, and cost summary',
        ],
    },
];

export const dashboardStats = {
    activeApiEndpoints: 5,
    fullyReadyModules: 2,
    uiPreviewModules: 7,
    totalDomainModules: adminModules.length,
};

export const getAdminModule = (key: AdminModuleKey) =>
    adminModules.find((module) => module.key === key) ?? adminModules[0];
