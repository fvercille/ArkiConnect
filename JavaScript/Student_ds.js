document.addEventListener('DOMContentLoaded', () => {
    const leftSidebar = document.getElementById('left-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const collapseToggle = document.getElementById('collapse-toggle');
    const menuToggleLeft = document.getElementById('menu-toggle-left');
    const menuToggleRight = document.getElementById('menu-toggle-right');
    const overlay = document.getElementById('mobile-overlay');

    // Store loaded data to prevent duplicates
    let loadedNotifications = new Set();
    let currentCalendarMonth = currentMonth;
    let currentCalendarYear = currentYear;

    // ===== SIDEBAR TOGGLE =====
    if (collapseToggle) {
        collapseToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('collapsed');
        });
    }

    if (menuToggleLeft && leftSidebar && overlay) {
        menuToggleLeft.addEventListener('click', () => {
            leftSidebar.classList.add('active');
            rightSidebar.classList.remove('active'); 
            overlay.classList.add('active');
        });
    }

    if (menuToggleRight && rightSidebar && overlay) {
        menuToggleRight.addEventListener('click', () => {
            rightSidebar.classList.add('active');
            leftSidebar.classList.remove('active'); 
            overlay.classList.add('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            leftSidebar.classList.remove('active');
            rightSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    // ===== ALERT MESSAGE =====
    window.alertMessage = function(message, title = "Message") {
        let messageBox = document.getElementById('temp-message-box');
        
        if (!messageBox) {
            messageBox = document.createElement('div');
            messageBox.id = 'temp-message-box';
            messageBox.style.cssText = `
                position: fixed; top: 10%; left: 50%; transform: translateX(-50%); 
                background-color: var(--primary-color, #a43825); color: white; padding: 15px 30px; 
                border-radius: var(--border-radius-sm, 8px); z-index: 10000; box-shadow: var(--shadow-md);
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

    // ===== ESCAPE HTML =====
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ===== FORMAT TIME AGO =====
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

    // ===== LOAD EVENTS FROM DATABASE =====
    async function loadEventsFromDatabase() {
        const studentEventsContainer = document.getElementById('student-event-list');
        if (!studentEventsContainer) return;

        try {
            const response = await fetch('event_api.php?action=get_events');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Events loaded:', data);

            if (!data.success) {
                console.error('API Error:', data.error);
                return;
            }

            const events = data.events || [];
            studentEventsContainer.innerHTML = '';

            if (events.length === 0) {
                studentEventsContainer.innerHTML = `
                    <div style="text-align: center; color: var(--text-muted); padding: 20px; font-size: var(--font-sm);">
                        <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        No upcoming events currently available.
                    </div>
                `;
                return;
            }

            events.forEach(event => {
                const eventDate = new Date(event.date);
                if (isNaN(eventDate)) return;

                const day = eventDate.getDate();
                const month = eventDate.toLocaleString('en-US', { month: 'short' }).toUpperCase();

                const eventCard = document.createElement('div');
                eventCard.setAttribute('data-event-id', event.id);

                const hostLogoPath = `../Images/${event.hostOrg || 'default-org'}.jpg`;
                const isRegistered = event.registered === true || event.registered === 1;

                eventCard.style.cssText = `
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    padding: 16px;
                    margin-bottom: 12px;
                    background: #ffffff;
                    border: 1px solid rgba(164,56,37,0.08);
                    border-radius: 14px;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
                    transition: box-shadow 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
                    cursor: default;
                    position: relative;
                    overflow: hidden;
                `;
                eventCard.onmouseenter = () => {
                    eventCard.style.boxShadow = '0 8px 24px rgba(164,56,37,0.1)';
                    eventCard.style.transform = 'translateY(-2px)';
                    eventCard.style.borderColor = 'rgba(164,56,37,0.25)';
                };
                eventCard.onmouseleave = () => {
                    eventCard.style.boxShadow = '0 2px 6px rgba(0,0,0,0.04)';
                    eventCard.style.transform = 'translateY(0)';
                    eventCard.style.borderColor = 'rgba(164,56,37,0.08)';
                };

                eventCard.innerHTML = `
                    <!-- Top row: date box + title + register button -->
                    <div style="display: flex; align-items: flex-start; gap: 12px;">

                        <!-- Date box -->
                        <div style="
                            min-width: 48px; height: 52px;
                            background: #a43825;
                            border-radius: 10px;
                            display: flex; flex-direction: column;
                            align-items: center; justify-content: center;
                            color: white; flex-shrink: 0;
                        ">
                            <span style="font-size: 0.58rem; font-weight: 700; letter-spacing: 0.06em; opacity: 0.85;">${month}</span>
                            <span style="font-size: 1.2rem; font-weight: 800; line-height: 1.1;">${day}</span>
                        </div>

                        <!-- Title + description -->
                        <div style="flex-grow: 1; overflow: hidden;">
                            <div style="font-weight: 700; font-size: 0.92rem; color: #1a1a1a; margin-bottom: 3px; line-height: 1.3;">
                                ${escapeHtml(event.title)}
                            </div>
                            <div style="font-size: 0.78rem; color: #666; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                ${escapeHtml(event.description || 'No description provided.')}
                            </div>
                        </div>

                        <!-- Register button -->
                        <button class="register-btn" data-event-id="${event.id}" 
                            onclick="registerForEventDB(${event.id}, this)"
                            style="
                                flex-shrink: 0;
                                background: ${isRegistered ? '#10B981' : '#a43825'};
                                color: white; border: none;
                                padding: 8px 14px;
                                border-radius: 8px;
                                font-weight: 700;
                                font-size: 0.78rem;
                                cursor: ${isRegistered ? 'default' : 'pointer'};
                                transition: background 0.2s ease;
                                display: flex; align-items: center; gap: 6px;
                                white-space: nowrap;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                            "
                            ${isRegistered ? 'disabled' : ''}>
                            <i class="fas ${isRegistered ? 'fa-check' : 'fa-plus'}"></i>
                            ${isRegistered ? 'Registered' : 'Register'}
                        </button>
                    </div>

                    <!-- Bottom row: org logo + name + registrant count -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 8px; border-top: 1px solid rgba(164,56,37,0.08);">
                        <div style="display: flex; align-items: center; gap: 7px;">
                            <img src="${hostLogoPath}" alt="${escapeHtml(event.hostOrg || 'Org')} Logo"
                                onerror="this.onerror=null;this.src='../Images/default-org.png';"
                                loading="lazy"
                                style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(164,56,37,0.2);">
                            <span style="font-size: 0.75rem; color: #555; font-weight: 500;">
                                ${escapeHtml(event.hostOrg || 'Unknown')}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px; font-size: 0.75rem; color: #a43825; font-weight: 600;">
                            <i class="fas fa-users" style="font-size: 0.7rem;"></i>
                            <span id="registrants-count-${event.id}">${event.registrants || 0}</span>
                            <span style="color: #888; font-weight: 400;">registrants</span>
                        </div>
                    </div>
                `;

                studentEventsContainer.appendChild(eventCard);
            });

            // ===== UPDATE FEATURED EVENT CARD =====
            if (events.length > 0) {
                const featured = events[0];
                window.featuredEventId = featured.id;

                // Featured button is intentionally locked to TBA — do not override
                const btn = document.getElementById('featured-register-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = 'Registration Opening Soon<i class="fas fa-clock"></i>';
                    btn.style.background = 'white';
                    btn.style.color = '#a43825';
                    btn.style.cursor = 'not-allowed';
                    btn.onclick = null;
                }
            }

        } catch (error) {
            console.error('Error loading events:', error);
        }
    }

    // ===== REGISTER FOR EVENT WITH EMAIL =====
    window.registerForEventDB = async function(eventId, buttonElement) {
        if (buttonElement.disabled) return;
        if (buttonElement._registering) return;
        
        buttonElement._registering = true;
        buttonElement.disabled = true;
        const originalHTML = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';

        try {
            const response = await fetch('event_api.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ event_id: eventId })
            });

            const data = await response.json();

            if (data.success) {
                buttonElement.innerHTML = '<i class="fas fa-check"></i> Registered!';
                buttonElement.style.backgroundColor = '#10B981';
                buttonElement.style.cursor = 'default';
                buttonElement.disabled = true;
                buttonElement.onclick = null;

                const countEl = document.getElementById(`registrants-count-${eventId}`);
                if (countEl) {
                    countEl.textContent = parseInt(countEl.textContent || 0) + 1;
                }

                alertMessage('Registration successful! Check your email for confirmation.', 'Success');

                setTimeout(() => {
                    loadNotificationsFromDatabase();
                    loadRegisteredEventsFromDatabase();
                    loadCalendarEventsForMonth(currentCalendarYear, currentCalendarMonth);
                }, 1000);
            } else {
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalHTML;
                alertMessage(data.error || 'Registration failed', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            buttonElement.disabled = false;
            buttonElement._registering = false;
            buttonElement.innerHTML = originalHTML;
            alertMessage('Connection error. Please try again.', 'Error');
        }
    }

    // ===== LOAD NOTIFICATIONS FROM DATABASE =====
    async function loadNotificationsFromDatabase() {
        try {
            const response = await fetch('event_api.php?action=get_notifications');
            const data = await response.json();

            if (!data.success) return;

            const notifications = data.notifications || [];
            updateNotificationUI(notifications);
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    // ===== UPDATE NOTIFICATION UI =====
    function updateNotificationUI(notifications) {
        const notifContainer = document.getElementById('notifications-container');
        if (!notifContainer) return;

        notifContainer.innerHTML = '';

        if (notifications.length === 0) {
            notifContainer.innerHTML = `
                <div style="text-align: center; color: #999; padding: 15px; font-size: 0.9rem;">
                    <i class="fas fa-bell-slash" style="display: block; margin-bottom: 5px;"></i>
                    No notifications yet
                </div>
            `;
            return;
        }

        notifications.forEach(notif => {
            const notifEl = document.createElement('div');
            notifEl.className = 'notification-item';
            notifEl.style.cssText = `
                padding: 10px 4px;
                margin-bottom: 4px;
                cursor: pointer;
                transition: opacity 0.2s ease;
            `;
            notifEl.onmouseenter = () => notifEl.style.boxShadow = '0 2px 8px rgba(164,56,37,0.12)';
            notifEl.onmouseleave = () => notifEl.style.boxShadow = 'none';

            let iconClass = 'fa-bell';
            let bgColor = '#dbeafe';
            let iconColor = '#1e40af';

            if (notif.notification_type === 'new_event') {
                iconClass = 'fa-calendar-plus';
                bgColor = '#fef3c7';
                iconColor = '#92400e';
            } else if (notif.notification_type === 'registration') {
                iconClass = 'fa-check-circle';
                bgColor = '#dcfce7';
                iconColor = '#15803d';
            } else if (notif.notification_type === 'event_approved') {
                iconClass = 'fa-thumbs-up';
                bgColor = '#dbeafe';
                iconColor = '#1e40af';
            }

            const timeAgo = formatTimeAgo(notif.created_at);

            notifEl.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <div style="
                        width: 38px; height: 38px; flex-shrink: 0;
                        border-radius: 10px;
                        background: ${bgColor};
                        color: ${iconColor};
                        display: flex; align-items: center; justify-content: center;
                        font-size: 0.85rem;
                    ">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div style="flex-grow: 1; overflow: hidden;">
                        <div style="font-weight: 700; font-size: 0.82rem; color: #1a1a1a; line-height: 1.3; margin-bottom: 4px;">
                            ${escapeHtml(notif.message)}
                        </div>
                        <div style="font-size: 0.72rem; color: #aaa; font-weight: 500;">
                            <i class="fas fa-clock" style="margin-right: 3px;"></i>${timeAgo}
                        </div>
                    </div>
                </div>
            `;

            notifEl.addEventListener('click', () => markNotificationRead(notif.id));
            notifContainer.appendChild(notifEl);
        });
    }

    // ===== LOAD REGISTERED EVENTS =====
    async function loadRegisteredEventsFromDatabase() {
        const registeredContainer = document.getElementById('registered-events-list');
        if (!registeredContainer) return;

        try {
            const response = await fetch('event_api.php?action=get_registered_events');
            const data = await response.json();

            if (!data.success) return;

            const events = data.events || [];
            registeredContainer.innerHTML = '';

            if (events.length === 0) {
                registeredContainer.innerHTML = `
                    <li style="text-align: center; color: #999; padding: 15px; font-size: 0.9rem;">
                        <i class="fas fa-bookmark"></i> No registered events
                    </li>
                `;
                return;
            }

            events.forEach(event => {
                const eventDate = new Date(event.date);
                const day = eventDate.getDate();
                const month = eventDate.toLocaleString('en-US', { month: 'short' });

                const li = document.createElement('li');
                li.style.cssText = `
                    list-style: none;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 8px 4px;
                    margin-bottom: 2px;
                    border-bottom: 1px solid rgba(164,56,37,0.08);
                    cursor: default;
                    transition: opacity 0.2s ease;
                `;
                li.onmouseenter = () => li.style.opacity = '0.7';
                li.onmouseleave = () => li.style.opacity = '1';

                li.innerHTML = `
                    <div style="
                        width: 34px; height: 34px; flex-shrink: 0;
                        border-radius: 50%;
                        background: #fde8e4;
                        color: #a43825;
                        display: flex; flex-direction: column;
                        align-items: center; justify-content: center;
                        font-size: 0.55rem; font-weight: 800;
                        line-height: 1.1;
                        text-align: center;
                    ">
                        <span>${month.toUpperCase()}</span>
                        <span style="font-size: 0.75rem;">${day}</span>
                    </div>
                    <div style="flex-grow: 1; overflow: hidden;">
                        <div style="font-weight: 600; font-size: 0.82rem; color: #1a1a1a; line-height: 1.35; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${escapeHtml(event.title)}
                        </div>
                        <div style="font-size: 0.72rem; color: #bbb;">
                            ${escapeHtml(event.organizer || 'Unknown')}
                        </div>
                    </div>
                    <span style="font-size: 0.65rem; font-weight: 500; color: #bbb; flex-shrink: 0; white-space: nowrap;">Upcoming</span>
                `;
                registeredContainer.appendChild(li);
            });
        } catch (error) {
            console.error('Error loading registered events:', error);
        }
    }

    // ===== LOAD ATTENDED EVENTS =====
    async function loadAttendedEventsFromDatabase() {
        const attendedContainer = document.getElementById('attended-events-list');
        if (!attendedContainer) return;

        try {
            const response = await fetch('event_api.php?action=get_attended_events');
            const data = await response.json();

            if (!data.success) return;

            const events = data.events || [];
            attendedContainer.innerHTML = '';

            if (events.length === 0) {
                attendedContainer.innerHTML = `
                    <li style="text-align: center; color: #999; padding: 15px; font-size: 0.9rem;">
                        <i class="fas fa-check-circle"></i> No events attended yet
                    </li>
                `;
                return;
            }

            events.forEach(event => {
                const eventDate = new Date(event.date);
                const day = eventDate.getDate();
                const month = eventDate.toLocaleString('en-US', { month: 'short' });

                const li = document.createElement('li');
                li.className = 'event-list-item';
                li.innerHTML = `
                    <div class="date-box">${month} ${day}</div>
                    <div class="info">
                        <div class="title">${escapeHtml(event.title)}</div>
                        <div class="org">${escapeHtml(event.organizer || 'Unknown')}</div>
                        <span class="attended-badge">Attended</span>
                    </div>
                `;
                attendedContainer.appendChild(li);
            });
        } catch (error) {
            console.error('Error loading attended events:', error);
        }
    }

    // ===== MARK NOTIFICATION AS READ =====
    window.markNotificationRead = async function(notificationId) {
        try {
            await fetch('event_api.php?action=mark_notification_read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: notificationId })
            });
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // ===== FETCH CALENDAR EVENTS FOR A GIVEN MONTH =====
    // ✅ FIX: This is now OUTSIDE renderCalendar() so the arrow buttons can call it
    async function loadCalendarEventsForMonth(year, month) {
        try {
            const response = await fetch(`event_api.php?action=get_calendar_events&year=${year}&month=${month}`);
            const data = await response.json();
            if (!data.success) return;

            // Rebuild eventDates for the new month
            Object.keys(eventDates).forEach(k => delete eventDates[k]);
            Object.assign(eventDates, data.eventDates || {});

            renderCalendar();
        } catch (error) {
            console.error('Error loading calendar events:', error);
        }
    }

    // ===== RENDER CALENDAR WITH REAL EVENTS =====
    function renderCalendar() {
        const calendarGrid = document.querySelector(".calendar-grid");
        const title = document.querySelector(".calendar-card h3");
        const date = new Date(currentCalendarYear, currentCalendarMonth - 1, 1);
        const monthName = date.toLocaleString('en-US', { month: 'long' });
        
        if (title) title.textContent = `${monthName} ${currentCalendarYear} Calendar`;
        if (!calendarGrid) return;

        calendarGrid.innerHTML = `
            <div class="day-header">SUN</div>
            <div class="day-header">MON</div>
            <div class="day-header">TUE</div>
            <div class="day-header">WED</div>
            <div class="day-header">THU</div>
            <div class="day-header">FRI</div>
            <div class="day-header">SAT</div>
        `;

        const firstDay = date.getDay();
        const daysInMonth = new Date(currentCalendarYear, currentCalendarMonth, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            calendarGrid.innerHTML += `<div class="date-cell empty"></div>`;
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const formattedDate = `${currentCalendarYear}-${String(currentCalendarMonth).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            
            const hasEvent = eventDates && eventDates[formattedDate];
            const classes = ["date-cell"];

            if (hasEvent) {
                classes.push("has-event");
                classes.push("event-highlight");
            }

            const today = new Date();
            if (
                day === today.getDate() &&
                currentCalendarMonth - 1 === today.getMonth() &&
                currentCalendarYear === today.getFullYear()
            ) {
                classes.push("current-day");
            }

            calendarGrid.innerHTML += `
                <div class="${classes.join(' ')}" data-event="${hasEvent || ''}">
                    <span class="date-number">${day}</span>
                    ${hasEvent ? '<div class="event-dots"><span class="event-dot personal"></span></div>' : ''}
                </div>
            `;
        }

        document.querySelectorAll(".date-cell.event-highlight").forEach(cell => {
            cell.addEventListener("click", () => {
                const eventName = cell.dataset.event;
                if (eventName) {
                    alertMessage(`Event: ${eventName}`, "Calendar Event");
                }
            });
        });
    }

    // ===== CALENDAR NAVIGATION =====
    function setupCalendarNavigation() {
        const prevButton = document.querySelector(".fa-chevron-left")?.parentElement;
        const nextButton = document.querySelector(".fa-chevron-right")?.parentElement;

        if (prevButton) {
            const cleanPrev = prevButton.cloneNode(true);
            prevButton.parentNode.replaceChild(cleanPrev, prevButton);
            cleanPrev.addEventListener('click', () => {
                if (currentCalendarMonth === 1) {
                    currentCalendarMonth = 12;
                    currentCalendarYear--;
                } else {
                    currentCalendarMonth--;
                }
                loadCalendarEventsForMonth(currentCalendarYear, currentCalendarMonth);
            });
        }

        if (nextButton) {
            const cleanNext = nextButton.cloneNode(true);
            nextButton.parentNode.replaceChild(cleanNext, nextButton);
            cleanNext.addEventListener('click', () => {
                if (currentCalendarMonth === 12) {
                    currentCalendarMonth = 1;
                    currentCalendarYear++;
                } else {
                    currentCalendarMonth++;
                }
                loadCalendarEventsForMonth(currentCalendarYear, currentCalendarMonth);
            });
        }
    }

    // ===== INITIAL LOAD =====
    console.log('🚀 Initializing Student Dashboard...');
    renderCalendar();
    setupCalendarNavigation();
    loadEventsFromDatabase();
    loadNotificationsFromDatabase();
    loadRegisteredEventsFromDatabase();
    loadAttendedEventsFromDatabase();

    // ===== REAL-TIME SYNC =====
    console.log('⏱️ Setting up real-time sync...');
    
    setInterval(() => { loadEventsFromDatabase(); }, 20000);
    setInterval(() => { loadNotificationsFromDatabase(); }, 10000);
    setInterval(() => { loadRegisteredEventsFromDatabase(); }, 15000);
    setInterval(() => { loadAttendedEventsFromDatabase(); }, 30000);
    
    console.log('Student Dashboard ready!');
});

lucide.createIcons();