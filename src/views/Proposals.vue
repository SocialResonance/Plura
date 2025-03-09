<template>
    <div class="plura-proposals">
        <h1>{{ t('plura', 'Proposals') }}</h1>
        
        <div class="placeholder-content">
            <p>{{ t('plura', 'This is a placeholder for the Proposals section.') }}</p>
            <p>{{ t('plura', 'The full implementation will be part of the next phase of development.') }}</p>
            
            <div class="proposals-list">
                <!-- This is a placeholder element for Cypress tests -->
            </div>
            
            <button class="primary" data-cy="new-proposal-button" disabled>
                {{ t('plura', 'Create New Proposal') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Proposals'
}
</script>

<style scoped lang="scss">
.plura-proposals {
    padding: 20px;
    
    h1 {
        margin-bottom: 20px;
    }
    
    .placeholder-content {
        background-color: var(--color-main-background);
        border: 1px solid var(--color-border);
        border-radius: 3px;
        padding: 20px;
        text-align: center;
        
        p {
            margin-bottom: 10px;
        }
        
        button {
            margin-top: 20px;
        }
    }
}
</style>