const LOGIN_PAGE_PATH = 'login.html'; 

window.logoutUser = function() {
    if (confirm('Are you sure you want to log out?')) {
        localStorage.removeItem('isAuthenticated');
        localStorage.removeItem('userRole');
        localStorage.removeItem('loginID');
        window.alertMessage('You have been successfully logged out.', 'Logout Success'); 
        setTimeout(() => {
            window.location.href = LOGIN_PAGE_PATH;
        }, 1000); 
    } else {
        window.alertMessage('Logout aborted.', 'Action');
    }
}

window.alertMessage = function(message, title = "Message") {
    const container = document.querySelector('.dashboard-container') || document.body;
    let messageBox = document.getElementById('temp-message-box');
    
    if (!messageBox) {
        messageBox = document.createElement('div');
        messageBox.id = 'temp-message-box';
        messageBox.style.cssText = `
            position: fixed; top: 10%; left: 50%; transform: translateX(-50%); 
            background-color: var(--primary-color, #a43825); color: white; padding: 15px 30px; 
            border-radius: 8px; z-index: 10000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-weight: 600; cursor: pointer; opacity: 0; transition: opacity 0.3s ease, transform 0.3s ease;
            min-width: 250px; text-align: center;
        `;
        document.body.appendChild(messageBox); 
        messageBox.onclick = () => messageBox.remove();
    } 
    messageBox.textContent = `${title}: ${message}`;
    messageBox.style.opacity = 1;
    messageBox.style.transform = 'translateY(0%) translateX(-50%)';

    setTimeout(() => {
        messageBox.style.opacity = 0;
        messageBox.style.transform = 'translateY(-20px) translateX(-50%)';
        setTimeout(() => {
            if (document.body.contains(messageBox)) {
                messageBox.remove();
            }
        }, 300);
    }, 3000);
}

window.redirectToCreateEvent = function() {
    window.location.href = 'create_event.php'; 
}

window.viewEvent = function(eventId) {
    alertMessage(`Viewing event details for Event ID: ${eventId}`, 'View Action');
}

window.editEvent = function(eventId) {
    window.location.href = `create_event.php?editId=${eventId}`; 
}

window.cancelEvent = function(eventId) {
    if (confirm(`Are you sure you want to cancel the event with ID ${eventId}? This action cannot be undone.`)) {
        // In the real implementation, this would call a PHP endpoint
        alertMessage(`Event ID ${eventId} cancellation request sent.`, 'Action');
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const leftSidebar = document.getElementById('left-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const collapseToggle = document.getElementById('collapse-toggle');
    const dashboardToggle = document.getElementById('dashboard-toggle');
    const dashboardSubmenu = document.getElementById('dashboard-submenu');
    const menuToggleLeft = document.getElementById('menu-toggle-left');
    const menuToggleRight = document.getElementById('menu-toggle-right');
    const overlay = document.getElementById('mobile-overlay');
    const dashboardContainer = document.getElementById('dashboard-container');

    // Collapse toggle for desktop sidebar
    if (collapseToggle && leftSidebar && dashboardContainer) {
        collapseToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('collapsed');
            dashboardContainer.classList.toggle('sidebar-collapsed');
        });
    }

    // Dashboard submenu toggle
    if (dashboardToggle && dashboardSubmenu) {
        dashboardToggle.addEventListener('click', (e) => {
            e.preventDefault(); 
            dashboardToggle.classList.toggle('active-toggle');
            dashboardSubmenu.classList.toggle('active-sub-menu');
        });
    }

    // Mobile menu toggle - LEFT sidebar
    if (menuToggleLeft && leftSidebar && overlay) {
        menuToggleLeft.addEventListener('click', () => {
            leftSidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            overlay.setAttribute('aria-hidden', overlay.classList.contains('active') ? 'false' : 'true');
        });
    }

    // Mobile menu toggle - RIGHT sidebar
    if (menuToggleRight && rightSidebar && overlay) {
        menuToggleRight.addEventListener('click', () => {
            rightSidebar.classList.add('active');
            leftSidebar.classList.remove('active');
            overlay.classList.add('active');
            document.body.classList.add('sidebar-open');
        });
    }

    // Close sidebars when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', () => {
            leftSidebar.classList.remove('active');
            rightSidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebars with ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (leftSidebar) leftSidebar.classList.remove('active');
            if (rightSidebar) rightSidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });

    // Load real-time notifications on page load
    loadOrgRepNotifications();
    
    // Refresh notifications every 30 seconds for real-time updates
    setInterval(loadOrgRepNotifications, 30000);
});

/**
 * Load real-time notifications for Organization Representatives
 * Shows registrations, cancellations, and event updates from database
 */
async function loadOrgRepNotifications() {
    try {
        const response = await fetch('event_api.php?action=get_notifications');
        const data = await response.json();
        
        if (!data.success) {
            console.warn('Failed to load notifications:', data.error || 'Unknown error');
            return;
        }
        
        const notifications = data.notifications || [];
        updateOrgRepNotificationUI(notifications);
    } catch (error) {
        console.error('Error loading OrgRep notifications:', error);
    }
}

/**
 * Update OrgRep notification UI with real-time database data
 * This replaces the static HTML notifications with live data
 */
function updateOrgRepNotificationUI(notifications) {
    // Find the Event Notifications section in the right sidebar
    const notifSection = document.getElementById('notifications-section');
    
    if (!notifSection) {
        console.warn('Notification section not found');
        return;
    }
    
    // Remove old notification items but keep the header
    const oldNotifications = notifSection.querySelectorAll('.notification-item');
    oldNotifications.forEach(n => n.remove());
    
    // Find where to insert notifications (after the header div)
    const headerDiv = notifSection.querySelector('div[style*="display: flex"]');
    
    if (notifications.length === 0) {
        const noNotif = document.createElement('div');
        noNotif.style.cssText = 'text-align: center; color: #9ca3af; padding: 20px; font-size: 0.9rem;';
        noNotif.innerHTML = `
            <i class="fas fa-inbox" style="display: block; margin-bottom: 10px; font-size: 1.5rem;"></i>
            No new notifications
        `;
        notifSection.appendChild(noNotif);
        return;
    }
    
    // Display most recent 3 notifications (matching PHP limit)
    notifications.slice(0, 3).forEach((notif) => {
        const notifEl = document.createElement('div');
        notifEl.className = 'notification-item';
        notifEl.style.cssText = `
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s ease;
            background-color: ${notif.isRead ? '#ffffff' : '#fef2f2'};
            cursor: pointer;
        `;
        
        // Determine icon based on notification type
        let iconClass = 'fa-user-check';
        let iconBg = '#dcfce7';
        let iconColor = '#15803d';
        
        if (notif.type === 'cancellation') {
            iconClass = 'fa-user-minus';
            iconBg = '#fee2e2';
            iconColor = '#b91c1c';
        } else if (notif.type === 'event_update') {
            iconClass = 'fa-bell';
            iconBg = '#dbeafe';
            iconColor = '#1e40af';
        }
        
        const timeAgo = formatTimeAgo(notif.timestamp);
        const userName = notif.userName ? ` (${notif.userName})` : '';
        
        notifEl.innerHTML = `
            <div style="display: flex; gap: 12px; align-items: center;">
                <div class="notification-icon" style="background-color: ${iconBg}; color: ${iconColor}; flex-shrink: 0; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="notification-content" style="flex-grow: 1; min-width: 0;">
                    <div class="notification-title" style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                        ${escapeHtml(notif.message)}
                    </div>
                    <div class="notification-time" style="font-size: 0.85rem; color: #9ca3af;">
                        ${timeAgo}${userName}
                    </div>
                </div>
                ${!notif.isRead ? '<div style="width: 8px; height: 8px; background: #a43825; border-radius: 50%; flex-shrink: 0;"></div>' : ''}
            </div>
        `;
        
        notifEl.addEventListener('click', () => markNotificationRead(notif.id, notifEl));
        notifEl.addEventListener('mouseover', () => {
            notifEl.style.backgroundColor = '#f9fafb';
        });
        notifEl.addEventListener('mouseout', () => {
            notifEl.style.backgroundColor = notif.isRead ? '#ffffff' : '#fef2f2';
        });
        
        notifSection.appendChild(notifEl);
    });
}

/**
 * Format timestamp for human-readable display
 */
function formatTimeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins === 1) return '1 minute ago';
    if (diffMins < 60) return `${diffMins} minutes ago`;
    if (diffHours === 1) return '1 hour ago';
    if (diffHours < 24) return `${diffHours} hours ago`;
    if (diffDays === 1) return '1 day ago';
    if (diffDays < 7) return `${diffDays} days ago`;
    
    return date.toLocaleDateString();
}

/**
 * Mark notification as read when clicked
 */
async function markNotificationRead(notificationId, element) {
    try {
        const response = await fetch('event_api.php?action=mark_notification_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ notification_id: notificationId })
        });
        
        const data = await response.json();
        
        if (data.success && element) {
            element.style.backgroundColor = '#ffffff';
            const indicator = element.querySelector('div[style*="background: #a43825"]');
            if (indicator) indicator.style.display = 'none';
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

/**
 * Escape HTML special characters to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}