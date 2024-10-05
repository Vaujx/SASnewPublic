<?php

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Calendar with Full Day Time Grid</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .calendar-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 1000px;
            padding: 20px;
        }
        .service-selector {
            margin-bottom: 20px;
        }
        .service-selector select {
            padding: 5px;
            font-size: 16px;
        }
        .calendar-header {
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .calendar-body {
            display: flex;
            gap: 20px;
        }
        .calendar-days-container {
            flex: 1;
        }
        .calendar-week-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .calendar-day {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            cursor: pointer;
            position: relative;
        }
        .calendar-day:hover {
            background-color: #f0f0f0;
        }
        .calendar-day.selected {
            background-color: var(--secondary-color);
            font-weight: bold;
        }
        .calendar-day.has-event::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
        }
        .time-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            padding: 10px;
            background-color: var(--secondary-color);
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }
        .time-slot {
            padding: 5px;
            text-align: center;
            background-color: white;
            border-radius: 3px;
            cursor: pointer;
        }
        .time-slot:hover {
            background-color: var(--primary-color);
            color: white;
        }
        .time-slot.selected {
            background-color: var(--primary-color);
            color: white;
        }
        .event-form {
            margin-top: 20px;
        }
        .event-form input, .event-form button, .event-form select {
            margin: 5px 0;
            padding: 5px;
            width: 100%;
            box-sizing: border-box;
        }
        .event-list {
            margin-top: 10px;
        }
        .event-item {
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .event-item button {
            color: white;
            border: none;
            padding: 2px 5px;
            cursor: pointer;
            border-radius: 3px;
        }
        /* Service-specific colors */
        .counseling {
            --primary-color: #4CAF50;
            --secondary-color: #E8F5E9;
        }
        .exam {
            --primary-color: #2196F3;
            --secondary-color: #E3F2FD;
        }
        .test {
            --primary-color: #FFC107;
            --secondary-color: #FFF8E1;
        }
        .calendar-header, .event-item button {
            background-color: var(--primary-color);
        }
        .calendar-day.has-event::after {
            background-color: var(--primary-color);
        }
        .event-item {
            background-color: var(--secondary-color);
        }
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .alert.success {
            background-color: #4CAF50;
        }
        .alert.error {
            background-color: #f44336;
        }
        .today-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="service-selector">
            <select id="serviceSelector">
                <option value="counseling">Counseling Session</option>
                <option value="exam">Entrance Exam</option>
                <option value="test">Psychology Test</option>
            </select>
        </div>
        <div class="calendar-header">
            <button id="prevMonth">&lt;</button>
            <span id="currentMonth"></span>
            <button id="nextMonth">&gt;</button>
            <button id="todayButton" class="today-button">Today</button>
        </div>
        <div class="calendar-body">
            <div class="calendar-days-container">
                <div class="calendar-week-days">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div class="calendar-days" id="calendarDays"></div>
            </div>
            <div class="time-grid" id="timeGrid"></div>
        </div>
        <div class="event-form" id="eventForm">
            <input type="text" id="eventTitle" placeholder="Event Title">
            <button id="addEvent">Add Event</button>
            <button id="updateEvent" style="display:none;">Update Event</button>
            <button id="cancelEdit" style="display:none;">Cancel</button>
        </div>
        <div class="event-list" id="eventList"></div>
    </div>
    <div id="alert" class="alert"></div>

    <script>
        let currentDate = new Date();
        let selectedDate = null;
        let selectedTime = null;
        let events = {};
        let editingEventId = null;
        let currentService = 'counseling';

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            document.getElementById('currentMonth').textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';

            for (let i = 0; i < startingDay; i++) {
                calendarDays.appendChild(document.createElement('div'));
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.textContent = day;
                dayElement.classList.add('calendar-day');
                dayElement.addEventListener('click', () => selectDate(year, month, day));

                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                if (events[dateString] && events[dateString].length > 0) {
                    dayElement.classList.add('has-event');
                }

                if (selectedDate && selectedDate.getDate() === day && selectedDate.getMonth() === month && selectedDate.getFullYear() === year) {
                    dayElement.classList.add('selected');
                }

                calendarDays.appendChild(dayElement);
            }
        }

        function renderTimeGrid() {
            const timeGrid = document.getElementById('timeGrid');
            timeGrid.innerHTML = '';

            for (let hour = 0; hour < 24; hour++) {
                for (let minute of ['00', '15', '30', '45']) {
                    const timeSlot = document.createElement('div');
                    timeSlot.classList.add('time-slot');
                    const time = `${hour.toString().padStart(2, '0')}:${minute}`;
                    timeSlot.textContent = time;
                    timeSlot.addEventListener('click', () => selectTime(time));
                    timeGrid.appendChild(timeSlot);
                }
            }
        }

        function selectDate(year, month, day) {
            selectedDate = new Date(year, month, day);
            selectedTime = null;
            renderCalendar();
            renderTimeGrid();
            fetchEvents();
        }

        function selectTime(time) {
            selectedTime = time;
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.toggle('selected', slot.textContent === time);
            });
            document.getElementById('eventForm').style.display = 'block';
        }

        function fetchEvents() {
            const dateString = selectedDate.toISOString().split('T')[0];
            fetch(`get_events.php?date=${dateString}&service=${currentService}`)
                .then(response => response.json())
                .then(data => {
                    events[dateString] = data;
                    renderEvents();
                })
                .catch(error => console.error('Error:', error));
        }

        function renderEvents() {
            const eventList = document.getElementById('eventList');
            eventList.innerHTML = '';

            if (selectedDate) {
                const dateString = selectedDate.toISOString().split('T')[0];
                const dayEvents = events[dateString] || [];

                dayEvents.forEach(event => {
                    const eventItem = document.createElement('div');
                    eventItem.classList.add('event-item');
                    eventItem.innerHTML = `
                        <span>${event.title} - ${event.time}</span>
                        <div>
                            <button onclick="editEvent(${event.id})">Edit</button>
                            <button onclick="deleteEvent(${event.id})">Delete</button>
                        </div>
                    `;
                    eventList.appendChild(eventItem);
                });
            }
        }

        function addEvent() {
            const title = document.getElementById('eventTitle').value;

            if (title && selectedDate && selectedTime) {
                const dateString = selectedDate.toISOString().split('T')[0];
                const data = new FormData();
                data.append('date', dateString);
                data.append('title', title);
                data.append('time', selectedTime);
                data.append('service', currentService);

                fetch('add_event.php', {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchEvents();
                        renderCalendar();
                        resetForm();
                        showAlert('Event added successfully!', 'success');
                    } else {
                        console.error('Failed to add event:', result.message);
                        showAlert('Failed to add event. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                });
            }
        }

        function editEvent(id) {
            const dateString = selectedDate.toISOString().split('T')[0];
            const event = events[dateString].find(e => e.id === id);
            document.getElementById('eventTitle').value = event.title;
            selectedTime = event.time;
            document.getElementById('addEvent').style.display = 'none';
            document.getElementById('updateEvent').style.display = 'inline-block';
            document.getElementById('cancelEdit').style.display = 'inline-block';
            editingEventId = id;

            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.toggle('selected', slot.textContent === event.time);
            });
        }

        function updateEvent() {
            const title = document.getElementById('eventTitle').value;

            if (title && selectedDate && selectedTime && editingEventId !== null) {
                const data = new FormData();
                data.append('id', editingEventId);
                data.append('title', title);
                data.append('time', selectedTime);
                data.append('service', currentService);

                fetch('update_event.php', {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchEvents();
                        resetForm();
                        showAlert('Event updated successfully!', 'success');
                    } else {
                        console.error('Failed to update event:', result.message);
                        showAlert('Failed to update event. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                });
            }
        }

        function deleteEvent(id) {
            fetch(`delete_event.php?id=${id}&service=${currentService}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchEvents();
                        renderCalendar();
                        showAlert('Event deleted successfully!', 'success');
                    } else {
                        console.error('Failed to delete event:', result.message);
                        showAlert('Failed to delete event. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                });
        }

        function resetForm() {
            document.getElementById('eventTitle').value = '';
            document.getElementById('addEvent').style.display = 'inline-block';
            document.getElementById('updateEvent').style.display = 'none';
            document.getElementById('cancelEdit').style.display = 'none';
            editingEventId = null;
            selectedTime = null;
            document.querySelectorAll('.time-slot').forEach(slot => slot.classList.remove('selected'));
        }

        function updateServiceColors() {
            document.body.className = currentService;
        }

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert ${type}`;
            alert.style.opacity = '1';
            setTimeout(() => {
                alert.style.opacity = '0';
            }, 3000);
        }

        function goToToday() {
            currentDate = new Date();
            selectDate(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        document.getElementById('todayButton').addEventListener('click', goToToday);

        document.getElementById('addEvent').addEventListener('click', addEvent);
        document.getElementById('updateEvent').addEventListener('click', updateEvent);
        document.getElementById('cancelEdit').addEventListener('click', resetForm);

        document.getElementById('serviceSelector').addEventListener('change', (e) => {
            currentService = e.target.value;
            updateServiceColors();
            renderCalendar();
            if (selectedDate) {
                fetchEvents();
            }
        });

        // Initialize the calendar
        updateServiceColors();
        renderCalendar();
        renderTimeGrid();
        goToToday(); // This will set the initial date to today and render the calendar
    </script>
</body>
</html>