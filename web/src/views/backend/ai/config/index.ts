import { FormItemRule } from 'element-plus'

export interface State {
    loading: boolean
    form: {
        ai_api_type: string
        ai_api_key: string
        ai_api_url: string
        ai_proxy: string
        ai_work_mode: string
        ai_accurate_hit: number
        ai_model: string
        ai_greeting: string
        ai_system_prompt: string
        ai_prompt: string
        ai_prompt_no_reference: string
        ai_short_memory: number
        ai_effective_match_kbs: number
        ai_effective_kbs_count: number
        ai_irrelevant_determination: number
        ai_irrelevant_message: string
        ai_vector_index: string
        ai_vector_similarity: string
        ai_initial_cap: number
        ai_flat_block_size: number
        ai_hnsw_m: number
        ai_hnsw_ef_construction: number
        ai_hnsw_knn_ef_runtime: number
        ai_hnsw_epsilon: number
        ai_allow_users_close_kbs: boolean
        ai_allow_users_select_kbs: boolean
        ai_allow_users_edit_prompt: boolean
        ai_allow_users_edit_tokens: boolean
        ai_allow_users_edit_effective_kbs: boolean
        ai_send_kbs_update_status: boolean
        auto_new_session_interval: number
        ai_session_title: string
        ai_session_logo: string
        ai_gift_tokens: number
        ai_score_exchange_tokens: number
        ai_money_exchange_tokens: number
    }
    rules: {
        [key: string]: FormItemRule[]
    }
    formKey: string
    activeTab: string
    submitLoading: boolean
    apiModelNames: anyObj
    chatModelAttr: anyObj
    embeddingModelAttr: anyObj
    embeddingModelSelectContent: anyObj
}
