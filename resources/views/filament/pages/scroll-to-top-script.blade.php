<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for modal open events
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                // Check if modal was added to DOM
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Check if it's a modal or dialog
                            if (node.classList && (node.classList.contains('fi-modal') || node.classList.contains('modal') || node.tagName === 'DIALOG')) {
                                // Scroll to top
                                setTimeout(() => {
                                    const scrollable = node.querySelector('[role="dialog"]') || node.querySelector('.overflow-y-auto') || node;
                                    if (scrollable) {
                                        scrollable.scrollTop = 0;
                                    }
                                }, 100);
                            }
                        }
                    });
                }
            });
        });

        // Start observing the document for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Also listen for Alpine.js modal events
        if (window.Alpine) {
            document.addEventListener('alpine:init', function() {
                // Scroll modals to top when they open
                const scrollToTopOnModalOpen = () => {
                    const modals = document.querySelectorAll('[role="dialog"]');
                    modals.forEach(modal => {
                        const scrollable = modal.querySelector('.overflow-y-auto') || modal;
                        scrollable.scrollTop = 0;
                    });
                };

                // Listen for Livewire events
                if (window.Livewire) {
                    Livewire.on('openModal', scrollToTopOnModalOpen);
                }
            });
        }
    });
</script>
