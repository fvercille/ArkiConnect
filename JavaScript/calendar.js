document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('event-modal');
const closeModal = document.querySelector('.event-modal-close');

if (closeModal) {
  closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
  });
}

window.addEventListener('click', (e) => {
  if (e.target === modal) {
    modal.style.display = 'none';
  }
});

    const leftSidebar = document.getElementById('left-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const collapseToggle = document.getElementById('collapse-toggle');
    const dashboardToggle = document.getElementById('dashboard-toggle');
    const dashboardSubmenu = document.getElementById('dashboard-submenu');
    const menuToggleLeft = document.getElementById('menu-toggle-left');
    const menuToggleRight = document.getElementById('menu-toggle-right');
    const overlay = document.getElementById('mobile-overlay');

    if (collapseToggle) {
        collapseToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('collapsed');
        });
    }

    if (dashboardToggle && dashboardSubmenu) {
        dashboardToggle.addEventListener('click', (e) => {
            e.preventDefault(); 
            dashboardToggle.classList.toggle('active-toggle');
            dashboardSubmenu.classList.toggle('active-sub-menu');
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

    window.alertMessage = function(message, title = "Message") {
        const container = document.querySelector('.dashboard-container') || document.body;
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

    function loadEventsForStudents() {
        const studentEventsContainer = document.getElementById('student-event-list');
        const events = JSON.parse(localStorage.getItem('dashboardEvents')) || [];
        if (!studentEventsContainer) return;
        studentEventsContainer.innerHTML = '';
        if (events.length === 0) {
            studentEventsContainer.innerHTML = `<p style="text-align: center; color: var(--text-muted); padding: 20px; font-size: var(--font-sm);">No upcoming events currently available.</p>`;
            return;
        }
        events.forEach(event => {
            const eventDate = new Date(event.date);
            if (isNaN(eventDate)) return; 
            const day = eventDate.getDate();
            const month = eventDate.toLocaleString('en-US', { month: 'short' }).toUpperCase(); 
            const eventCard = document.createElement('div');
            eventCard.className = 'event-item-card'; 
            const hostLogoPath = `../Images/${event.hostOrg || 'default-org'}.jpg`; 
            const isRegistered = false;
            const buttonText = isRegistered ? 'Registered!' : 'Register Now';
            const buttonStyle = isRegistered ? `background: var(--success-color, #10B981); cursor: default;` : `background: var(--primary-color);`;
            const buttonDisabled = isRegistered ? 'disabled' : '';
            eventCard.innerHTML = `
                <div class="event-date-box">
                    <div class="month">${month}</div>
                    <div class="day">${day}</div>
                </div>
                <div class="event-info"> 
                    <p style="font-weight: 600; font-size: var(--font-lg); color: var(--text-dark); margin-bottom: 2px; line-height: 1.2;">
                        ${event.title}
                    </p>
                    <p style="font-size: var(--font-sm); color: var(--text-muted); margin-bottom: 8px; line-height: 1.3;">
                        ${event.description || 'No description provided.'}
                    </p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: var(--font-xs); color: var(--text-muted); display: flex; align-items: center;">
                            <img class="host-logo" src="${hostLogoPath}" alt="${event.hostOrg} Logo" onerror="this.onerror=null;this.src='../Images/default-org.png';" loading="lazy">
                            Hosted by <span style="font-weight: 500; margin-left: 4px; color: var(--text-dark);">${event.hostOrg || 'Unknown'}</span>
                        </span>
                        <div style="font-size: var(--font-sm); color: var(--primary-color); font-weight: 500;">
                            <i class="fas fa-users" style="margin-right: 4px;"></i> 
                            <span id="registrants-count-${event.id}">${event.registrants}</span> Registrants
                        </div>
                    </div>
                </div>
                <div style="flex-shrink: 0; display: flex; align-items: center;">
                    <button class="register-btn" ${buttonDisabled} onclick="registerForEvent('${event.id}', this)" style="
                        ${buttonStyle} color: white; border: none; padding: 8px 12px; 
                        border-radius: var(--border-radius-sm); font-weight: 600; cursor: pointer; 
                        transition: all var(--transition-fast); font-size: var(--font-sm);
                        box-shadow: var(--shadow-sm);
                    ">
                        <i class="fas fa-check"></i> ${buttonText}
                    </button>
                </div>
            `;
            studentEventsContainer.appendChild(eventCard);
        });
    }

    window.registerForEvent = function(eventId, buttonElement) {
        let events = JSON.parse(localStorage.getItem('dashboardEvents')) || [];
        const eventIndex = events.findIndex(event => String(event.id) === eventId);
        if (eventIndex !== -1) {
            events[eventIndex].registrants = (events[eventIndex].registrants || 0) + 1; 
            localStorage.setItem('dashboardEvents', JSON.stringify(events));
            alertMessage(`Registered successfully for ${events[eventIndex].title}!`, 'Registration Success');
            const countElement = document.getElementById(`registrants-count-${eventId}`);
            if (countElement) {
                countElement.textContent = events[eventIndex].registrants;
            }
            if (buttonElement) {
                 buttonElement.disabled = true;
                 buttonElement.innerHTML = '<i class="fas fa-check"></i> Registered!';
                 buttonElement.style.backgroundColor = 'var(--success-color, #10B981)'; 
                 buttonElement.style.cursor = 'default';
            }
        }
    }

    // ===== EVENT CALENDAR PAGE LOGIC =====
    // Check if we're on the event calendar page
    const calendarGrid = document.getElementById('calendar-grid');
    
    if (calendarGrid) {
        // This is the EVENT CALENDAR page
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        let currentMonth = 9; // October (0-indexed)
        let currentYear = 2025;

        const prevBtn = document.getElementById('prev-month-btn');
        const nextBtn = document.getElementById('next-month-btn');
        const monthDisplay = document.getElementById('calendar-month-display');




        function renderEventCalendar() {
            calendarGrid.innerHTML = '';
            monthDisplay.textContent = `${monthNames[currentMonth]} ${currentYear}`;

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const lastDate = new Date(currentYear, currentMonth + 1, 0).getDate();

            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            // Day headers
            days.forEach(day => {
                const header = document.createElement('div');
                header.classList.add('day-header');
                header.textContent = day;
                calendarGrid.appendChild(header);
            });

            // Empty cells before first day
            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.classList.add('date-cell', 'empty');
                calendarGrid.appendChild(empty);
            }

            // Date cells
     for (let d = 1; d <= lastDate; d++) {
    const cell = document.createElement('div');
    cell.classList.add('date-cell');

    // Check if this day is today (real time)
    const now = new Date();
    const isToday = (
        d === now.getDate() &&
        currentMonth === now.getMonth() &&
        currentYear === now.getFullYear()
    );
    if (isToday) {
      cell.classList.add('today');
    }

    const dateNum = document.createElement('span');
    dateNum.classList.add('date-number');
    dateNum.textContent = d;
    cell.appendChild(dateNum);

    const dateKey = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    if (eventDates[dateKey]) {
      const eventData = eventDates[dateKey];
      const eventName = document.createElement('div');
      eventName.className = 'event-name';
      eventName.textContent = eventData.title;
      eventName.style.cursor = 'pointer';
      eventName.addEventListener('click', () => {
        const urlParams = new URLSearchParams();
        urlParams.set('event', dateKey);
        window.location.href = `event_details.php`;
      });
      cell.appendChild(eventName);
    }

    calendarGrid.appendChild(cell);
}
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderEventCalendar();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                renderEventCalendar();
            });
        }

        renderEventCalendar();
    } else {
        // This is the DASHBOARD page with mini calendar
        const mockToday = new Date(2025, 9, 1);
        const highlightedDates = {
            "2025-10-12": "INBLOOM: Exploring the Architect in Nature",
            "2025-10-25": "QUADRIPARTITE 2025"
        };

        const minDate = new Date(2025, 9, 1);
        const maxDate = new Date(2026, 3, 30);

        let currentYear = 2025, currentMonth = 9;

        function renderCalendar() {
            const dashboardCalendarGrid = document.querySelector(".calendar-grid");
            const title = document.querySelector(".calendar-card h3");
            const date = new Date(currentYear, currentMonth, 1);
            const monthName = date.toLocaleString('en-US', { month: 'long' });
            if (title) title.textContent = `${monthName} ${currentYear} Calendar`;
            if (!dashboardCalendarGrid) return; 
            dashboardCalendarGrid.innerHTML = `
                <div class="day-header">SUN</div>
                <div class="day-header">MON</div>
                <div class="day-header">TUE</div>
                <div class="day-header">WED</div>
                <div class="day-header">THU</div>
                <div class="day-header">FRI</div>
                <div class="day-header">SAT</div>
            `;
            const firstDay = date.getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            for (let i = 0; i < firstDay; i++) {
                dashboardCalendarGrid.innerHTML += `<div class="date-cell empty"></div>`;
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const fullDate = new Date(currentYear, currentMonth, day);
                const formattedDate = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
                const classes = ["date-cell"];
                if (fullDate < minDate || fullDate > maxDate) classes.push("disabled");
                if (fullDate.toDateString() === mockToday.toDateString()) classes.push("current-day");
                if (highlightedDates[formattedDate]) classes.push("event-highlight");
                dashboardCalendarGrid.innerHTML += `
                    <div class="${classes.join(' ')}" data-event="${highlightedDates[formattedDate] || ''}">
                        <span class="date-number">${day}</span>
                    </div>
                `;
            }
            document.querySelectorAll(".date-cell.event-highlight").forEach(cell => {
                cell.addEventListener("click", () => {
                    const eventName = cell.dataset.event;
                    alertMessage(`Event on this day: ${eventName}`, "Calendar Event");
                });
            });
            const prevButton = document.querySelector(".fa-chevron-left");
            const nextButton = document.querySelector(".fa-chevron-right");
            if (prevButton) prevButton.parentElement.onclick = prevMonth;
            if (nextButton) nextButton.parentElement.onclick = nextMonth;
        }

        function prevMonth() {
            if (currentYear === 2025 && currentMonth === 9) {
                console.log("This is the start of your academic year.");
                return;
            }
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar();
        }

        function nextMonth() {
            if (currentYear === 2026 && currentMonth === 3) {
                console.log("This is the end of your academic year.");
                return;
            }
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar();
        }

        renderCalendar();
    }

    const logoutButton = document.getElementById('logout-button');

    if (logoutButton) {
        logoutButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                localStorage.clear(); 
                window.location.href = 'login.html'; 
            }
        });
    }

    loadEventsForStudents();
});

// Assuming you have variables for day, month, year in your loop
const today = new Date();
const isToday = (year === today.getFullYear() && month === today.getMonth() && day === today.getDate());

const dateCell = document.createElement('div');
dateCell.classList.add('date-cell');
if (isToday) {
  dateCell.classList.add('today');
}   
