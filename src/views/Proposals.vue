<template>
    <div class="plura-proposals">
        <div class="proposals-header">
            <h1>{{ t('plura', 'Proposals') }}</h1>
            
            <div class="proposals-actions">
                <button class="primary" data-cy="new-proposal-button" @click="showCreateForm = true">
                    {{ t('plura', 'Create New Proposal') }}
                </button>
                
                <div class="proposals-sorting">
                    <label>{{ t('plura', 'Sort by:') }}</label>
                    <select v-model="sortBy" @change="loadProposals" data-cy="sort-select">
                        <option value="priority">{{ t('plura', 'Priority') }}</option>
                        <option value="created_at">{{ t('plura', 'Newest') }}</option>
                        <option value="deadline">{{ t('plura', 'Deadline') }}</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Loading state -->
        <div v-if="loading" class="proposals-loading">
            <NcLoadingIcon />
            <span>{{ t('plura', 'Loading proposals...') }}</span>
        </div>
        
        <!-- Error state -->
        <div v-else-if="error" class="proposals-error">
            <span>{{ error }}</span>
            <button @click="loadProposals">{{ t('plura', 'Retry') }}</button>
        </div>
        
        <!-- Empty state -->
        <div v-else-if="proposals.length === 0" class="proposals-empty">
            <div class="icon-info"></div>
            <h2>{{ t('plura', 'No proposals yet') }}</h2>
            <p>{{ t('plura', 'Be the first to create a proposal!') }}</p>
            <button class="primary" @click="showCreateForm = true">
                {{ t('plura', 'Create New Proposal') }}
            </button>
        </div>
        
        <!-- Proposals list -->
        <div v-else class="proposals-list">
            <div 
                v-for="proposal in proposals" 
                :key="proposal.id" 
                class="proposal-item"
                @click="openProposal(proposal.id)"
            >
                <div class="proposal-priority">
                    <span class="proposal-priority-label">{{ t('plura', 'Priority') }}</span>
                    <span class="proposal-priority-score" data-cy="proposal-priority-score">{{ Math.round(proposal.priority_score) }}</span>
                </div>
                
                <div class="proposal-content">
                    <h2 class="proposal-title">{{ proposal.title }}</h2>
                    <p class="proposal-description">{{ truncate(proposal.description, 150) }}</p>
                    
                    <div class="proposal-meta">
                        <div class="proposal-created-at" :data-timestamp="proposal.created_at">
                            {{ t('plura', 'Created') }}: {{ formatDate(proposal.created_at) }}
                        </div>
                        <div class="proposal-deadline" :data-timestamp="proposal.deadline">
                            {{ t('plura', 'Deadline') }}: {{ formatDate(proposal.deadline) }}
                        </div>
                        <div class="proposal-status" :class="'status-' + proposal.status">
                            {{ formatStatus(proposal.status) }}
                        </div>
                    </div>
                </div>
                
                <div class="proposal-credits">
                    <span class="proposal-credits-label">{{ t('plura', 'Credits') }}</span>
                    <span class="proposal-credits-amount">{{ formatNumber(proposal.credits_allocated) }}</span>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="proposals-pagination">
                <button @click="prevPage" :disabled="offset === 0">
                    {{ t('plura', 'Previous') }}
                </button>
                <span>{{ t('plura', 'Page {page}', { page: currentPage }) }}</span>
                <button @click="nextPage" :disabled="proposals.length < limit">
                    {{ t('plura', 'Next') }}
                </button>
            </div>
        </div>
        
        <!-- Create proposal form dialog -->
        <div v-if="showCreateForm" class="proposal-form-overlay">
            <div class="proposal-form-dialog">
                <div class="proposal-form-header">
                    <h2>{{ t('plura', 'Create New Proposal') }}</h2>
                    <button class="close-button" @click="showCreateForm = false">×</button>
                </div>
                
                <div class="proposal-form">
                    <div class="form-field">
                        <label for="proposal-title">{{ t('plura', 'Title') }} *</label>
                        <input 
                            type="text" 
                            id="proposal-title" 
                            v-model="newProposal.title" 
                            data-cy="proposal-title-input"
                            :placeholder="t('plura', 'Enter a concise title')" 
                            required
                        >
                    </div>
                    
                    <div class="form-field">
                        <label for="proposal-description">{{ t('plura', 'Description') }} *</label>
                        <textarea 
                            id="proposal-description" 
                            v-model="newProposal.description" 
                            data-cy="proposal-description-input"
                            :placeholder="t('plura', 'Describe what should be changed and why')" 
                            rows="5" 
                            required
                        ></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label for="proposal-document">{{ t('plura', 'Document') }} *</label>
                        <select 
                            id="proposal-document" 
                            v-model="newProposal.documentId" 
                            data-cy="document-selector"
                            required
                        >
                            <option value="" disabled>{{ t('plura', 'Select a document') }}</option>
                            <option 
                                v-for="doc in documents" 
                                :key="doc.id" 
                                :value="doc.id" 
                                class="document-option"
                                data-cy="document-option"
                            >
                                {{ doc.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="proposal-deadline">{{ t('plura', 'Deadline') }}</label>
                        <input 
                            type="date" 
                            id="proposal-deadline" 
                            v-model="newProposal.deadline" 
                            data-cy="proposal-deadline"
                            :min="minDate"
                        >
                    </div>
                    
                    <div class="form-actions">
                        <button class="primary" @click="createProposal" data-cy="proposal-submit-button" :disabled="isSubmitting">
                            <span v-if="isSubmitting">{{ t('plura', 'Creating...') }}</span>
                            <span v-else>{{ t('plura', 'Create Proposal') }}</span>
                        </button>
                        <button @click="showCreateForm = false">{{ t('plura', 'Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Proposal details dialog -->
        <div v-if="selectedProposal" class="proposal-details-overlay">
            <div class="proposal-details-dialog">
                <div class="proposal-details-header">
                    <h2>{{ selectedProposal.title }}</h2>
                    <button class="close-button" @click="selectedProposal = null">×</button>
                </div>
                
                <div class="proposal-details">
                    <div class="proposal-details-content">
                        <div class="proposal-meta">
                            <div class="proposal-creator">
                                {{ t('plura', 'Created by') }}: {{ selectedProposal.user_id }}
                            </div>
                            <div class="proposal-created-at">
                                {{ t('plura', 'Created') }}: {{ formatDate(selectedProposal.created_at) }}
                            </div>
                            <div class="proposal-deadline">
                                {{ t('plura', 'Deadline') }}: {{ formatDate(selectedProposal.deadline) }}
                            </div>
                            <div class="proposal-document">
                                {{ t('plura', 'Document') }}: {{ getDocumentName(selectedProposal.document_id) }}
                            </div>
                            <div class="proposal-status" :class="'status-' + selectedProposal.status">
                                {{ formatStatus(selectedProposal.status) }}
                            </div>
                        </div>
                        
                        <div class="proposal-description-full">
                            <h3>{{ t('plura', 'Description') }}</h3>
                            <div class="proposal-description-text">{{ selectedProposal.description }}</div>
                        </div>
                        
                        <div class="proposal-priority">
                            <h3>{{ t('plura', 'Priority Calculation') }}</h3>
                            <div class="priority-explanation">
                                <p>{{ t('plura', 'Priority is calculated using quadratic funding, which gives greater weight to proposals with broad support.') }}</p>
                            </div>
                            
                            <div class="priority-stats">
                                <div class="priority-stat">
                                    <span class="stat-label">{{ t('plura', 'Raw Credits') }}</span>
                                    <span class="stat-value raw-credits">{{ formatNumber(selectedProposalDetails?.calculation?.raw_credits || 0) }}</span>
                                </div>
                                <div class="priority-stat">
                                    <span class="stat-label">{{ t('plura', 'Quadratic Value') }}</span>
                                    <span class="stat-value quadratic-value">{{ formatNumber(selectedProposalDetails?.calculation?.quadratic_score || 0) }}</span>
                                </div>
                                <div class="priority-stat">
                                    <span class="stat-label">{{ t('plura', 'Matching Fund Bonus') }}</span>
                                    <span class="stat-value matching-fund-bonus">{{ formatNumber(selectedProposalDetails?.calculation?.matching_fund_bonus || 0) }}</span>
                                </div>
                                <div class="priority-stat total">
                                    <span class="stat-label">{{ t('plura', 'Total Priority Score') }}</span>
                                    <span class="stat-value total-priority-score">{{ formatNumber(selectedProposalDetails?.calculation?.quadratic_score || 0) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="proposal-credits-allocation">
                            <h3>{{ t('plura', 'Allocate Credits') }}</h3>
                            <p>{{ t('plura', 'Allocate your credits to increase this proposal\'s priority.') }}</p>
                            
                            <div class="allocation-form">
                                <input 
                                    type="number" 
                                    v-model="creditAmount" 
                                    min="1" 
                                    max="100" 
                                    data-cy="credit-allocation-input"
                                >
                                <button 
                                    class="primary" 
                                    @click="allocateCredits" 
                                    data-cy="allocate-credits-button"
                                    :disabled="allocating || creditAmount <= 0"
                                >
                                    <span v-if="allocating">{{ t('plura', 'Allocating...') }}</span>
                                    <span v-else>{{ t('plura', 'Allocate Credits') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'

export default {
    name: 'Proposals',
    components: {
        NcLoadingIcon
    },
    data() {
        return {
            proposals: [],
            loading: true,
            error: null,
            offset: 0,
            limit: 10,
            currentPage: 1,
            sortBy: 'priority',
            showCreateForm: false,
            isSubmitting: false,
            newProposal: {
                title: '',
                description: '',
                documentId: '',
                deadline: this.getDefaultDeadline()
            },
            documents: [],
            selectedProposal: null,
            selectedProposalDetails: null,
            creditAmount: 10,
            allocating: false
        }
    },
    computed: {
        minDate() {
            const today = new Date()
            return today.toISOString().split('T')[0]
        }
    },
    mounted() {
        this.loadProposals()
        this.loadDocuments()
    },
    methods: {
        loadProposals() {
            this.loading = true
            this.error = null
            
            // Determine orderBy and orderDirection based on sortBy
            let orderBy, orderDirection
            switch (this.sortBy) {
                case 'created_at':
                    orderBy = 'created_at'
                    orderDirection = 'DESC'
                    break
                case 'deadline':
                    orderBy = 'deadline'
                    orderDirection = 'ASC'
                    break
                case 'priority':
                default:
                    orderBy = 'credits_allocated'
                    orderDirection = 'DESC'
                    break
            }
            
            axios.get(generateUrl('/apps/plura/api/proposals'), {
                params: {
                    limit: this.limit,
                    offset: this.offset,
                    orderBy,
                    orderDirection
                }
            })
                .then(response => {
                    this.proposals = response.data
                    this.loading = false
                })
                .catch(error => {
                    console.error('Error loading proposals', error)
                    this.error = t('plura', 'Failed to load proposals')
                    this.loading = false
                })
        },
        
        loadDocuments() {
            // In a real implementation, this would load documents from the Nextcloud Files API
            // For now, we'll use dummy data
            this.documents = [
                { id: '1', name: 'Document 1.docx' },
                { id: '2', name: 'Project Plan.md' },
                { id: '3', name: 'Meeting Notes.txt' }
            ]
        },
        
        createProposal() {
            if (!this.newProposal.title || !this.newProposal.description || !this.newProposal.documentId) {
                // Show validation error
                return
            }
            
            this.isSubmitting = true
            
            axios.post(generateUrl('/apps/plura/api/proposals'), {
                title: this.newProposal.title,
                description: this.newProposal.description,
                documentId: this.newProposal.documentId,
                deadline: this.newProposal.deadline
            })
                .then(response => {
                    this.isSubmitting = false
                    this.showCreateForm = false
                    
                    // Reset form
                    this.newProposal = {
                        title: '',
                        description: '',
                        documentId: '',
                        deadline: this.getDefaultDeadline()
                    }
                    
                    // Reload proposals
                    this.loadProposals()
                })
                .catch(error => {
                    console.error('Error creating proposal', error)
                    this.isSubmitting = false
                    // Show error
                })
        },
        
        openProposal(id) {
            axios.get(generateUrl(`/apps/plura/api/proposals/${id}`))
                .then(response => {
                    this.selectedProposal = response.data
                    
                    // Load detailed information
                    return axios.get(generateUrl(`/apps/plura/api/proposals/${id}/details`))
                })
                .then(response => {
                    this.selectedProposalDetails = response.data
                })
                .catch(error => {
                    console.error('Error loading proposal details', error)
                })
        },
        
        allocateCredits() {
            if (this.creditAmount <= 0 || !this.selectedProposal) {
                return
            }
            
            this.allocating = true
            
            axios.post(generateUrl(`/apps/plura/api/proposals/${this.selectedProposal.id}/allocate`), {
                amount: parseFloat(this.creditAmount)
            })
                .then(response => {
                    this.allocating = false
                    
                    // Update proposal and details
                    this.selectedProposal = response.data.proposal
                    
                    // Reload proposal details
                    return axios.get(generateUrl(`/apps/plura/api/proposals/${this.selectedProposal.id}/details`))
                })
                .then(response => {
                    this.selectedProposalDetails = response.data
                    
                    // Reset credit amount
                    this.creditAmount = 10
                    
                    // Reload proposals list
                    this.loadProposals()
                })
                .catch(error => {
                    console.error('Error allocating credits', error)
                    this.allocating = false
                    // Show error
                })
        },
        
        prevPage() {
            if (this.offset >= this.limit) {
                this.offset -= this.limit
                this.currentPage--
                this.loadProposals()
            }
        },
        
        nextPage() {
            if (this.proposals.length === this.limit) {
                this.offset += this.limit
                this.currentPage++
                this.loadProposals()
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return ''
            
            const date = new Date(dateString)
            return date.toLocaleDateString()
        },
        
        formatNumber(number) {
            return new Intl.NumberFormat().format(number)
        },
        
        formatStatus(status) {
            const statusMap = {
                'open': t('plura', 'Open'),
                'closed': t('plura', 'Closed'),
                'completed': t('plura', 'Completed'),
                'canceled': t('plura', 'Canceled')
            }
            
            return statusMap[status] || status
        },
        
        truncate(text, length) {
            if (!text) return ''
            
            if (text.length <= length) {
                return text
            }
            
            return text.substring(0, length) + '...'
        },
        
        getDefaultDeadline() {
            const date = new Date()
            date.setDate(date.getDate() + 14) // 2 weeks from now
            return date.toISOString().split('T')[0]
        },
        
        getDocumentName(documentId) {
            const doc = this.documents.find(d => d.id === documentId)
            return doc ? doc.name : documentId
        }
    }
}
</script>

<style scoped lang="scss">
.plura-proposals {
    padding: 20px;
    
    .proposals-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        
        h1 {
            margin: 0;
        }
        
        .proposals-actions {
            display: flex;
            align-items: center;
            
            .proposals-sorting {
                margin-left: 20px;
                display: flex;
                align-items: center;
                
                label {
                    margin-right: 5px;
                }
            }
        }
    }
    
    .proposals-loading,
    .proposals-error,
    .proposals-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        text-align: center;
        
        h2 {
            margin-top: 20px;
        }
        
        p {
            margin-bottom: 20px;
        }
    }
    
    .proposals-list {
        background-color: var(--color-main-background);
        border: 1px solid var(--color-border);
        border-radius: 3px;
        
        .proposal-item {
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
            
            .proposal-priority {
                width: 80px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                
                .proposal-priority-label {
                    font-size: 12px;
                    color: var(--color-text-maxcontrast);
                }
                
                .proposal-priority-score {
                    font-size: 24px;
                    font-weight: bold;
                    color: var(--color-primary);
                }
            }
            
            .proposal-content {
                flex: 1;
                padding: 0 15px;
                
                .proposal-title {
                    margin-top: 0;
                    margin-bottom: 5px;
                }
                
                .proposal-description {
                    color: var(--color-text-maxcontrast);
                    margin-bottom: 10px;
                }
                
                .proposal-meta {
                    display: flex;
                    flex-wrap: wrap;
                    font-size: 12px;
                    color: var(--color-text-maxcontrast);
                    
                    > div {
                        margin-right: 15px;
                    }
                    
                    .proposal-status {
                        font-weight: bold;
                        
                        &.status-open {
                            color: green;
                        }
                        
                        &.status-closed {
                            color: orange;
                        }
                        
                        &.status-completed {
                            color: blue;
                        }
                        
                        &.status-canceled {
                            color: red;
                        }
                    }
                }
            }
            
            .proposal-credits {
                width: 100px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                
                .proposal-credits-label {
                    font-size: 12px;
                    color: var(--color-text-maxcontrast);
                }
                
                .proposal-credits-amount {
                    font-size: 18px;
                    font-weight: bold;
                }
            }
        }
        
        .proposals-pagination {
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
    
    .proposal-form-overlay,
    .proposal-details-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .proposal-form-dialog,
    .proposal-details-dialog {
        background-color: var(--color-main-background);
        border-radius: 3px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        
        .proposal-form-header,
        .proposal-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--color-border);
            
            h2 {
                margin: 0;
            }
            
            .close-button {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                
                &:hover {
                    background-color: var(--color-background-hover);
                }
            }
        }
        
        .proposal-form,
        .proposal-details {
            padding: 15px;
            overflow-y: auto;
            
            .form-field {
                margin-bottom: 15px;
                
                label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                }
                
                input, textarea, select {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid var(--color-border);
                    border-radius: 3px;
                }
                
                textarea {
                    resize: vertical;
                }
            }
            
            .form-actions {
                display: flex;
                justify-content: flex-end;
                margin-top: 20px;
                
                button {
                    margin-left: 10px;
                }
            }
        }
        
        .proposal-details-content {
            .proposal-meta {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 20px;
                
                > div {
                    margin-right: 20px;
                    margin-bottom: 10px;
                    
                    &:last-child {
                        margin-right: 0;
                    }
                }
                
                .proposal-status {
                    font-weight: bold;
                    
                    &.status-open {
                        color: green;
                    }
                    
                    &.status-closed {
                        color: orange;
                    }
                    
                    &.status-completed {
                        color: blue;
                    }
                    
                    &.status-canceled {
                        color: red;
                    }
                }
            }
            
            .proposal-description-full {
                margin-bottom: 20px;
                
                h3 {
                    margin-top: 0;
                    margin-bottom: 10px;
                }
                
                .proposal-description-text {
                    white-space: pre-line;
                }
            }
            
            .proposal-priority {
                margin-bottom: 20px;
                
                h3 {
                    margin-top: 0;
                    margin-bottom: 10px;
                }
                
                .priority-explanation {
                    margin-bottom: 15px;
                }
                
                .priority-stats {
                    display: flex;
                    flex-wrap: wrap;
                    
                    .priority-stat {
                        background-color: var(--color-background-dark);
                        border-radius: 3px;
                        padding: 10px;
                        margin-right: 10px;
                        margin-bottom: 10px;
                        min-width: 150px;
                        
                        &.total {
                            background-color: var(--color-primary-element);
                            color: white;
                        }
                        
                        .stat-label {
                            display: block;
                            font-size: 12px;
                            margin-bottom: 5px;
                        }
                        
                        .stat-value {
                            display: block;
                            font-size: 18px;
                            font-weight: bold;
                        }
                    }
                }
            }
            
            .proposal-credits-allocation {
                h3 {
                    margin-top: 0;
                    margin-bottom: 10px;
                }
                
                p {
                    margin-bottom: 15px;
                }
                
                .allocation-form {
                    display: flex;
                    
                    input {
                        width: 100px;
                        margin-right: 10px;
                        padding: 8px;
                        border: 1px solid var(--color-border);
                        border-radius: 3px;
                    }
                }
            }
        }
    }
}
</style>