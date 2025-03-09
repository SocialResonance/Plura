<template>
    <div class="plura-dashboard">
        <h1>{{ t('plura', 'Plura Dashboard') }}</h1>
        
        <div class="plura-dashboard-section">
            <h2>{{ t('plura', 'Welcome to Plura') }}</h2>
            <p>{{ t('plura', 'Plura is a collaborative document editing system powered by community wisdom.') }}</p>
            
            <div class="plura-stats">
                <div class="plura-stat-box">
                    <h3>{{ t('plura', 'Your Credits') }}</h3>
                    <div class="plura-stat-value">{{ formatNumber(userCredits) }}</div>
                </div>
                
                <div class="plura-stat-box">
                    <h3>{{ t('plura', 'Matching Fund') }}</h3>
                    <div class="plura-stat-value">{{ formatNumber(matchingFundTotal) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
    name: 'Dashboard',
    data() {
        return {
            userCredits: 0,
            matchingFundTotal: 0,
            loading: true,
            error: null,
        }
    },
    mounted() {
        // Get user credits and matching fund total
        this.fetchUserCredits()
        this.fetchMatchingFundTotal()
    },
    methods: {
        formatNumber(number) {
            return new Intl.NumberFormat().format(number)
        },
        fetchUserCredits() {
            axios.get(generateUrl('/apps/plura/api/credits'))
                .then((response) => {
                    this.userCredits = response.data.credit_amount
                    this.loading = false
                })
                .catch((error) => {
                    console.error('Error fetching user credits', error)
                    this.error = t('plura', 'Failed to load credit data')
                    this.loading = false
                })
        },
        fetchMatchingFundTotal() {
            axios.get(generateUrl('/apps/plura/api/matching-fund'))
                .then((response) => {
                    this.matchingFundTotal = response.data.total
                })
                .catch((error) => {
                    console.error('Error fetching matching fund total', error)
                })
        }
    }
}
</script>

<style scoped lang="scss">
.plura-dashboard {
    padding: 20px;
    
    h1 {
        margin-bottom: 20px;
    }
    
    .plura-dashboard-section {
        background-color: var(--color-main-background);
        border: 1px solid var(--color-border);
        border-radius: 3px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .plura-stats {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
        
        .plura-stat-box {
            flex: 1;
            min-width: 200px;
            background-color: var(--color-background-hover);
            border-radius: 3px;
            padding: 15px;
            margin-right: 15px;
            margin-bottom: 15px;
            
            h3 {
                font-size: 14px;
                margin-bottom: 10px;
            }
            
            .plura-stat-value {
                font-size: 24px;
                font-weight: bold;
                color: var(--color-primary);
            }
        }
    }
}
</style>