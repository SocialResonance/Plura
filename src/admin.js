import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'

// Initialize the admin panel
document.addEventListener('DOMContentLoaded', () => {
    // Save parameters button
    const saveButton = document.getElementById('save-parameters')
    if (saveButton) {
        saveButton.addEventListener('click', saveParameters)
    }
    
    // Add to matching fund button
    const addToFundButton = document.getElementById('add-to-fund')
    if (addToFundButton) {
        addToFundButton.addEventListener('click', addToMatchingFund)
    }
})

/**
 * Save all parameters
 */
function saveParameters() {
    const parameters = {
        k_value: document.getElementById('k_value').value,
        initial_credit_amount: document.getElementById('initial_credit_amount').value,
        matching_fund_percentage: document.getElementById('matching_fund_percentage').value,
        minimum_vote_threshold: document.getElementById('minimum_vote_threshold').value,
        proposal_funding_period_days: document.getElementById('proposal_funding_period_days').value,
        implementation_voting_period_hours: document.getElementById('implementation_voting_period_hours').value
    }
    
    // Create promises for all parameter updates
    const promises = Object.entries(parameters).map(([name, value]) => {
        return axios.post(generateUrl(`/settings/admin/plura/parameters`), {
            name,
            value
        })
    })
    
    // Execute all promises
    Promise.all(promises)
        .then(() => {
            showSuccess(t('plura', 'Parameters saved successfully'))
        })
        .catch(error => {
            console.error('Error saving parameters', error)
            showError(t('plura', 'Failed to save parameters'))
        })
}

/**
 * Add to the matching fund
 */
function addToMatchingFund() {
    const amount = document.getElementById('add_to_matching_fund').value
    
    axios.post(generateUrl(`/settings/admin/plura/matching-fund`), {
        amount
    })
        .then(response => {
            // Update the displayed total
            document.getElementById('matching_fund_total').value = 
                new Intl.NumberFormat().format(response.data.total)
                
            showSuccess(t('plura', 'Credits added to matching fund'))
        })
        .catch(error => {
            console.error('Error adding to matching fund', error)
            showError(t('plura', 'Failed to add credits to matching fund'))
        })
}