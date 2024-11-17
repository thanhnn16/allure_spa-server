import { defineStore } from 'pinia'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

export const useHistoryStore = defineStore('history', () => {
    const routeHistory = ref([])

    // Get history from localStorage
    function initHistory() {
        const history = localStorage.getItem('routeHistory')
        routeHistory.value = history ? JSON.parse(history) : []

        // Add current route if history is empty
        if (!routeHistory.value.length) {
            routeHistory.value.push(router.page.url)
            saveToStorage()
        }
    }

    // Save history to localStorage
    function saveToStorage() {
        localStorage.setItem('routeHistory', JSON.stringify(routeHistory.value))
    }

    // Add new route to history
    function addToHistory(url) {
        if (!routeHistory.value.length || routeHistory.value[routeHistory.value.length - 1] !== url) {
            routeHistory.value.push(url)
            saveToStorage()
        }
    }

    // Handle back navigation
    function goBack() {
        if (routeHistory.value.length > 1) {
            // Remove current route
            routeHistory.value.pop()
            saveToStorage()
            // Get previous route
            const previousRoute = routeHistory.value[routeHistory.value.length - 1]
            router.visit(previousRoute)
        }
    }

    // Check if can go back
    function canGoBack(currentUrl) {
        return currentUrl !== '/dashboard' && routeHistory.value.length > 1
    }

    return {
        routeHistory,
        initHistory,
        addToHistory,
        goBack,
        canGoBack
    }
}) 