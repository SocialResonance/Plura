<template>
    <div class="plura-credit-history">
        <h1>{{ t('plura', 'Credit Transaction History') }}</h1>
        
        <div class="transaction-history" v-if="!loading">
            <div class="plura-transaction-list-header">
                <div class="transaction-date">{{ t('plura', 'Date') }}</div>
                <div class="transaction-type">{{ t('plura', 'Type') }}</div>
                <div class="transaction-amount">{{ t('plura', 'Amount') }}</div>
            </div>
            
            <div v-if="transactions.length === 0" class="empty-list">
                {{ t('plura', 'No transactions yet') }}
            </div>
            
            <div v-else class="transaction-item" v-for="transaction in transactions" :key="transaction.id" @click="showTransactionDetails(transaction)">
                <div class="transaction-date">{{ formatDate(transaction.created_at) }}</div>
                <div class="transaction-type">{{ formatTransactionType(transaction.transaction_type) }}</div>
                <div class="transaction-amount" :class="{ 'positive': transaction.amount > 0, 'negative': transaction.amount < 0 }">
                    {{ transaction.amount > 0 ? '+' : '' }}{{ formatNumber(transaction.amount) }}
                </div>
            </div>
            
            <div class="pagination" v-if="transactions.length > 0">
                <button @click="loadPreviousPage" :disabled="offset === 0">
                    {{ t('plura', 'Previous') }}
                </button>
                <span>{{ t('plura', 'Page {page}', { page: Math.floor(offset / limit) + 1 }) }}</span>
                <button @click="loadNextPage" :disabled="transactions.length < limit">
                    {{ t('plura', 'Next') }}
                </button>
            </div>
        </div>
        
        <div v-if="loading" class="loading">
            {{ t('plura', 'Loading transactions...') }}
        </div>
        
        <div v-if="error" class="error">
            {{ error }}
        </div>
        
        <!-- Transaction details modal -->
        <div v-if="selectedTransaction" class="transaction-details">
            <h2>{{ t('plura', 'Transaction Details') }}</h2>
            <div class="transaction-details-content">
                <div class="transaction-detail-row">
                    <label>{{ t('plura', 'ID') }}</label>
                    <div>{{ selectedTransaction.id }}</div>
                </div>
                <div class="transaction-detail-row">
                    <label>{{ t('plura', 'Date') }}</label>
                    <div class="transaction-date">{{ formatDate(selectedTransaction.created_at) }}</div>
                </div>
                <div class="transaction-detail-row">
                    <label>{{ t('plura', 'Type') }}</label>
                    <div class="transaction-type">{{ formatTransactionType(selectedTransaction.transaction_type) }}</div>
                </div>
                <div class="transaction-detail-row">
                    <label>{{ t('plura', 'Amount') }}</label>
                    <div class="transaction-amount" :class="{ 'positive': selectedTransaction.amount > 0, 'negative': selectedTransaction.amount < 0 }">
                        {{ selectedTransaction.amount > 0 ? '+' : '' }}{{ formatNumber(selectedTransaction.amount) }}
                    </div>
                </div>
                <div class="transaction-detail-row" v-if="selectedTransaction.related_entity_id">
                    <label>{{ t('plura', 'Related Entity ID') }}</label>
                    <div class="transaction-related-entity">{{ selectedTransaction.related_entity_id }}</div>
                </div>
            </div>
            
            <div class="modal-buttons">
                <button class="primary" @click="selectedTransaction = null">{{ t('plura', 'Close') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
    name: 'CreditHistory',
    data() {
        return {
            transactions: [],
            loading: true,
            error: null,
            offset: 0,
            limit: 20,
            selectedTransaction: null
        }
    },
    mounted() {
        this.fetchTransactions()
    },
    methods: {
        formatNumber(number) {
            return new Intl.NumberFormat().format(number)
        },
        formatDate(dateString) {
            const date = new Date(dateString)
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
        },
        formatTransactionType(type) {
            const typeMap = {
                'initial_allocation': t('plura', 'Initial Allocation'),
                'proposal_fund': t('plura', 'Proposal Funding'),
                'implementation_reward': t('plura', 'Implementation Reward'),
                'vote_cost': t('plura', 'Vote Cost'),
                'prediction_cost': t('plura', 'Prediction Cost'),
                'prediction_reward': t('plura', 'Prediction Reward'),
                'admin_adjustment': t('plura', 'Admin Adjustment')
            }
            
            return typeMap[type] || type
        },
        fetchTransactions() {
            this.loading = true
            axios.get(generateUrl('/apps/plura/api/transactions'), {
                params: {
                    limit: this.limit,
                    offset: this.offset
                }
            })
                .then((response) => {
                    this.transactions = response.data
                    this.loading = false
                })
                .catch((error) => {
                    console.error('Error fetching transactions', error)
                    this.error = t('plura', 'Failed to load transactions')
                    this.loading = false
                })
        },
        loadPreviousPage() {
            if (this.offset >= this.limit) {
                this.offset -= this.limit
                this.fetchTransactions()
            }
        },
        loadNextPage() {
            if (this.transactions.length === this.limit) {
                this.offset += this.limit
                this.fetchTransactions()
            }
        },
        showTransactionDetails(transaction) {
            this.selectedTransaction = transaction
        }
    }
}
</script>

<style scoped lang="scss">
.plura-credit-history {
    padding: 20px;
    
    h1 {
        margin-bottom: 20px;
    }
    
    .transaction-history {
        background-color: var(--color-main-background);
        border: 1px solid var(--color-border);
        border-radius: 3px;
        
        .plura-transaction-list-header {
            display: flex;
            padding: 15px;
            font-weight: bold;
            border-bottom: 1px solid var(--color-border);
            background-color: var(--color-background-dark);
        }
        
        .transaction-item {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid var(--color-border);
            cursor: pointer;
            
            &:hover {
                background-color: var(--color-background-hover);
            }
            
            &:last-child {
                border-bottom: none;
            }
        }
        
        .transaction-date {
            flex: 2;
        }
        
        .transaction-type {
            flex: 2;
        }
        
        .transaction-amount {
            flex: 1;
            text-align: right;
            font-weight: bold;
            
            &.positive {
                color: green;
            }
            
            &.negative {
                color: red;
            }
        }
        
        .empty-list {
            padding: 20px;
            text-align: center;
            font-style: italic;
            color: var(--color-text-maxcontrast);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            padding: 15px;
            border-top: 1px solid var(--color-border);
            
            button {
                margin: 0 10px;
            }
            
            span {
                line-height: 36px;
            }
        }
    }
    
    .loading, .error {
        padding: 20px;
        text-align: center;
    }
    
    .error {
        color: var(--color-error);
    }
    
    .transaction-details {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: var(--color-main-background);
        border: 1px solid var(--color-border);
        border-radius: 3px;
        padding: 20px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        
        h2 {
            margin-bottom: 20px;
        }
        
        .transaction-details-content {
            margin-bottom: 20px;
        }
        
        .transaction-detail-row {
            display: flex;
            margin-bottom: 10px;
            
            label {
                flex: 1;
                font-weight: bold;
            }
            
            div {
                flex: 2;
            }
        }
        
        .modal-buttons {
            display: flex;
            justify-content: flex-end;
        }
    }
}
</style>