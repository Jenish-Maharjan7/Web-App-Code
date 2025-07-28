<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events</title>
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <div class="container">
        <h2>My Events</h2>

        <div class="nav-container">
            <a href="create_event.php" class="nav-button">Create New Event</a>
            <a href="dashboard.php" class="nav-button">Dashboard</a>
        </div>

        <div id="event-list">
            <p>Loading events...</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        loadEvents();

        function loadEvents() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'api/events.php?user_id=<?= $user_id ?>', true); // Pass user_id to API
            xhr.onload = function() {
                if (this.status === 200) {
                    const events = JSON.parse(this.responseText);
                    displayEvents(events);
                } else {
                    document.getElementById('event-list').innerHTML = '<p class="error-message">Error loading events.</p>';
                }
            };
            xhr.onerror = function() {
                document.getElementById('event-list').innerHTML = '<p class="error-message">Network error loading events.</p>';
            };
            xhr.send();
        }

        function displayEvents(events) {
            const eventListDiv = document.getElementById('event-list');
            eventListDiv.innerHTML = '';

            if (events.length === 0) {
                eventListDiv.innerHTML = '<p>You have not created any events yet.</p>';
                return;
            }

            events.forEach(event => {
                const eventCard = document.createElement('div');
                eventCard.classList.add('event-card');
                eventCard.innerHTML = `
                    <h3>${event.event_name}</h3>
                    <p><strong>Date:</strong> ${event.event_date}</p>
                    <p><strong>Time:</strong> ${event.event_time || 'N/A'}</p>
                    <p><strong>Location:</strong> ${event.location}</p>
                    <p><strong>Description:</strong> ${event.description || 'No description.'}</p>
                    <p><strong>Status:</strong> ${event.is_public == 1 ? 'Public' : 'Private'}</p>
                    <div class="event-actions">
                        <a href="edit_event.php?id=${event.event_id}" class="nav-button edit">Edit</a>
                        <button class="nav-button delete" data-event-id="${event.event_id}">Delete</button>
                    </div>
                `;
                eventListDiv.appendChild(eventCard);
            });

            eventListDiv.querySelectorAll('.delete').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.eventId;
                    if (confirm('Are you sure you want to delete this event?')) {
                        deleteEvent(eventId);
                    }
                });
            });
        }

        function deleteEvent(eventId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'api/events.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                const response = JSON.parse(this.responseText);
                if (this.status === 200 && response.success) {
                    alert(response.message);
                    loadEvents(); // Reload events after deletion
                } else {
                    alert(response.message || 'Error deleting event.');
                }
            };
            xhr.onerror = function() {
                alert('Network error deleting event.');
            };
            xhr.send(`action=delete&event_id=${eventId}`);
        }
    });
    </script>
</body>
</html>
