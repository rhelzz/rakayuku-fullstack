import { store } from '@/actions/App/Http/Controllers/Api/Material/MaterialFlowController';
import { apiRequest } from '@/services/apiClient';

export type MaterialFlowPayload = {
    project_id: number;
    required_qty: number;
    item_id?: number;
    item_code?: string;
    item_name?: string;
    item_unit?: string;
    base_price?: number;
    initial_stock?: number;
    minimum_stock?: number;
    auto_purchase: boolean;
    purchase?: {
        supplier_name?: string;
        unit_price?: number;
        qty?: number;
        notes?: string;
    };
    document_ref?: string;
    reference_note?: string;
};

export type MaterialFlowResult = {
    flow: {
        material_registered: boolean;
        stock_sufficient_initially: boolean;
        auto_purchase_used: boolean;
    };
    project: {
        id: number;
        code: string;
        name: string;
    };
    item: {
        id: number;
        code: string;
        name: string;
        unit: string;
        base_price: number;
        current_stock: number;
    };
    stock: {
        required_qty: number;
        stock_before: number;
        shortage_qty: number;
        purchased_qty: number;
        stock_after: number;
    };
    purchase: {
        performed: boolean;
        purchase_order: {
            id: number;
            po_number: string;
            supplier_name: string;
            status: string;
            total_amount: number;
        } | null;
        stock_in_transaction_id: number | null;
    };
    warehouse_issue: {
        stock_out_transaction_id: number;
        quantity: number;
        unit_price: number;
        total: number;
    };
    material_plan: {
        id: number;
        estimated_qty: number;
        estimated_total: number;
        actual_qty: number;
        actual_total: number;
    };
    project_cost_summary: {
        id: number;
        hpp_material: number;
        hpp_total: number;
        gross_profit: number;
        gross_margin_percent: number;
    };
};

export const materialFlowService = {
    async process(payload: MaterialFlowPayload, token: string) {
        const response = await apiRequest<MaterialFlowResult>(store(), {
            token,
            body: payload,
        });

        return response.data;
    },
};
