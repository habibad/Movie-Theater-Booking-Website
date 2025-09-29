/**
 * Seat Selection Page JavaScript
 * Handles seat selection, seat map interactions, and booking process
 */

(function($) {
    'use strict';

    const SeatSelection = {
        selectedSeats: [],
        currentZoom: 1,
        showtimeId: null,
        bookingData: {},

        init: function() {
            if (!$('.seat-map').length) return;

            this.showtimeId = $('.seat-map').data('showtime-id');
            this.loadSavedSeatSelection();
            this.initSeatClicks();
            this.initSeatControls();
            this.initContinueButton();
            this.initModalActions(); // Initialize modal actions here
            this.startAutoSave();
            this.preventAccidentalLeave();

            console.log('🪑 Seat selection initialized');
        },

        // ===== SEAT CLICKS =====
        initSeatClicks: function() {
            $(document).on('click', '.seat.available', function() {
                const $seat = $(this);
                const seatNumber = $seat.data('seat');
                const seatPrice = parseFloat($seat.data('price')) || 19.00;
                const seatType = $seat.data('type') || 'regular';
                
                if ($seat.hasClass('selected')) {
                    SeatSelection.deselectSeat($seat, seatNumber);
                } else {
                    if (SeatSelection.selectedSeats.length >= 8) {
                        SeatSelection.showNotification('Maximum 8 seats can be selected at once.', 'warning');
                        return;
                    }
                    SeatSelection.selectSeat($seat, seatNumber, seatPrice, seatType);
                }
                
                SeatSelection.updateSeatSummary();
            });
        },

        selectSeat: function($seat, seatNumber, seatPrice, seatType) {
            $seat.addClass('selected seat-animation');
            this.selectedSeats.push({
                number: seatNumber,
                price: seatPrice,
                type: seatType
            });
            
            setTimeout(() => {
                $seat.removeClass('seat-animation');
            }, 300);
        },

        deselectSeat: function($seat, seatNumber) {
            $seat.removeClass('selected');
            this.selectedSeats = this.selectedSeats.filter(seat => seat.number !== seatNumber);
        },

        updateSeatSummary: function() {
            const seatCount = this.selectedSeats.length;
            const seatNumbers = this.selectedSeats.map(seat => seat.number).join(', ');
            
            $('#seat-count').text(seatCount);
            $('#selected-seat-numbers').text(seatNumbers || 'None selected');
            
            const $continueBtn = $('#continue-to-cart');
            if (seatCount > 0) {
                $continueBtn.prop('disabled', false)
                           .removeClass('btn-disabled')
                           .html(`NEXT: CART <small>(${seatCount} seat${seatCount !== 1 ? 's' : ''})</small>`);
            } else {
                $continueBtn.prop('disabled', true)
                           .addClass('btn-disabled')
                           .text('SELECT A SEAT TO CONTINUE');
            }

            $('#form-selected-seats').val(seatNumbers);
        },

        // ===== SEAT MAP CONTROLS =====
        initSeatControls: function() {
            $('.btn-zoom-in').on('click', () => {
                this.zoomSeatMap(1.2);
            });

            $('.btn-zoom-out').on('click', () => {
                this.zoomSeatMap(0.8);
            });

            $('.btn-fullscreen').on('click', () => {
                this.toggleFullscreen();
            });
        },

        zoomSeatMap: function(factor) {
            const $seatMap = $('.seat-map');
            this.currentZoom = Math.max(0.5, Math.min(2.5, this.currentZoom * factor));
            
            $seatMap.css({
                'transform': `scale(${this.currentZoom})`,
                'transform-origin': 'center center'
            });
            
            $('.btn-zoom-out').prop('disabled', this.currentZoom <= 0.5);
            $('.btn-zoom-in').prop('disabled', this.currentZoom >= 2.5);
        },

        toggleFullscreen: function() {
            const element = $('.seat-selection-main')[0];
            
            if (!document.fullscreenElement) {
                if (element.requestFullscreen) {
                    element.requestFullscreen().catch(() => {
                        this.showNotification('Fullscreen not supported on this device.', 'info');
                    });
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        },

        // ===== CONTINUE TO CART =====
        initContinueButton: function() {
            $(document).on('click', '#continue-to-cart', (e) => {
                e.preventDefault();
                
                if (this.selectedSeats.length === 0) {
                    this.showNotification('Please select at least one seat to continue.', 'error');
                    return;
                }
                
                this.showSeatSelectionModal();
            });
        },

        showSeatSelectionModal: function() {
            const selectedSeatNumbers = this.selectedSeats.map(seat => seat.number).join(', ');
            
            if (!$('#seat-selection-modal').length) {
                this.createSeatSelectionModal();
            }
            
            $('#modal-selected-seats').text(selectedSeatNumbers + '/Recliner Seat');
            $('#seat-selection-modal').show();
        },

        createSeatSelectionModal: function() {
            const modalHTML = `
                <div id="seat-selection-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>SELECT YOUR TICKET</h3>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="selected-seat-info">
                                <p><strong>Seats Selected:</strong> <span id="modal-selected-seats"></span></p>
                                <p class="booking-fee-notice">A $2.25 booking fee per ticket is included in the price of the ticket.</p>
                            </div>
                            <div class="ticket-options">
                                <div class="ticket-type ora-member">
                                    <div class="ticket-details">
                                        <div class="member-badge">
                                            <span>Ora</span>
                                            <small>Cinépolis Rewards Member</small>
                                        </div>
                                        <div class="ticket-info">
                                            <select class="ticket-select">
                                                <option value="adult">Adult</option>
                                                <option value="child">Child</option>
                                                <option value="senior">Senior</option>
                                            </select>
                                            <small>2 ticket types available</small>
                                        </div>
                                    </div>
                                    <div class="ticket-price">
                                        <span class="price">$21.25</span>
                                        <small>($19.00 + $2.25 Booking Fee)</small>
                                        <button class="btn-add" data-type="ora-adult" data-price="21.25">ADD</button>
                                    </div>
                                </div>
                                <div class="ticket-type">
                                    <div class="ticket-details">
                                        <div class="ticket-info">
                                            <span class="ticket-label">Adult</span>
                                        </div>
                                    </div>
                                    <div class="ticket-price">
                                        <span class="price">$21.25</span>
                                        <small>($19.00 + $2.25 Booking Fee)</small>
                                        <button class="btn-add" data-type="adult" data-price="21.25">ADD</button>
                                    </div>
                                </div>
                                <div class="ticket-type">
                                    <div class="ticket-details">
                                        <div class="ticket-info">
                                            <span class="ticket-label">Senior (60+)</span>
                                        </div>
                                    </div>
                                    <div class="ticket-price">
                                        <span class="price">$19.25</span>
                                        <small>($17.00 + $2.25 Booking Fee)</small>
                                        <button class="btn-add" data-type="senior" data-price="19.25">ADD</button>
                                    </div>
                                </div>
                            </div>
                            <div class="member-options">
                                <h4>WOULD YOU LIKE TO PURCHASE TICKETS FOR OTHER MEMBERS?</h4>
                                <p>Link a member</p>
                                <div class="link-buttons">
                                    <button class="btn btn-dark" data-action="link">LINK</button>
                                    <button class="btn btn-dark" data-action="card">CARD</button>
                                    <button class="btn btn-dark" data-action="email">EMAIL</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHTML);
        },

        // ===== MODAL ACTIONS =====
        initModalActions: function() {
            // Handle Add button clicks
            $(document).on('click', '.btn-add', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const $btn = $(e.currentTarget);
                const ticketType = $btn.data('type') || 'adult';
                const ticketPrice = parseFloat($btn.data('price')) || 21.25;
                
                // Create booking data
                this.bookingData = {
                    showtime_id: this.showtimeId,
                    seats: this.selectedSeats,
                    ticket_type: ticketType,
                    ticket_price: ticketPrice,
                    total: this.selectedSeats.length * ticketPrice
                };
                
                // Close modal and proceed to cart
                $('#seat-selection-modal').hide();
                this.proceedToCart();
            });

            // Handle member linking buttons
            $(document).on('click', '.link-buttons .btn-dark', function() {
                const action = $(this).data('action');
                SeatSelection.showNotification(`${action.toUpperCase()} member linking is not available in demo mode.`, 'info');
            });

            // Handle modal close button
            $(document).on('click', '#seat-selection-modal .modal-close', function() {
                $('#seat-selection-modal').hide();
            });
        },

        proceedToCart: function() {
            const $continueBtn = $('#continue-to-cart');
            const originalText = $continueBtn.html();
            
            $continueBtn.prop('disabled', true).html('<div class="btn-spinner"></div> Processing...');
            
            const saveData = {
                showtime_id: this.showtimeId,
                seats: this.selectedSeats.map(seat => seat.number).join(','),
                booking_data: JSON.stringify(this.bookingData)
            };
            
            // Try to save booking data
            this.saveBookingData(saveData)
                .then(() => {
                    localStorage.removeItem('cinema_temp_booking');
                    // Redirect to cart
                    window.location.href = '/cart/';
                })
                .catch((error) => {
                    console.error('Save failed:', error);
                    // Use localStorage backup
                    localStorage.setItem('cinema_booking_backup', JSON.stringify(saveData));
                    // Still redirect to cart
                    window.location.href = '/cart/';
                })
                .finally(() => {
                    $continueBtn.prop('disabled', false).html(originalText);
                });
        },

        // ===== AUTO SAVE =====
        startAutoSave: function() {
            setInterval(() => {
                this.autoSaveSeatSelection();
            }, 30000);
        },

        autoSaveSeatSelection: function() {
            if (this.selectedSeats.length > 0) {
                const tempData = {
                    showtime_id: this.showtimeId,
                    seats: this.selectedSeats,
                    timestamp: Date.now()
                };
                localStorage.setItem('cinema_temp_booking', JSON.stringify(tempData));
            }
        },

        loadSavedSeatSelection: function() {
            const savedBooking = localStorage.getItem('cinema_temp_booking');
            if (savedBooking) {
                try {
                    const bookingData = JSON.parse(savedBooking);
                    
                    if (bookingData.showtime_id == this.showtimeId && 
                        (Date.now() - bookingData.timestamp) < 1800000) {
                        
                        this.selectedSeats = bookingData.seats || [];
                        this.selectedSeats.forEach(seat => {
                            $(`.seat[data-seat="${seat.number}"]`).addClass('selected');
                        });
                        this.updateSeatSummary();
                        
                        if (this.selectedSeats.length > 0) {
                            this.showNotification(`${this.selectedSeats.length} seat(s) restored from previous session.`, 'info');
                        }
                    }
                } catch (e) {
                    console.error('Error loading saved booking:', e);
                }
            }
        },

        // ===== PREVENT ACCIDENTAL LEAVE =====
        preventAccidentalLeave: function() {
            $(window).on('beforeunload', () => {
                if (this.selectedSeats.length > 0) {
                    return 'You have selected seats. Are you sure you want to leave?';
                }
            });

            window.addEventListener('popstate', () => {
                if (this.selectedSeats.length > 0) {
                    const shouldLeave = confirm('You have selected seats. Are you sure you want to leave this page?');
                    if (!shouldLeave) {
                        history.pushState(null, null, location.href);
                    }
                }
            });
        },

        // ===== UTILITY FUNCTIONS =====
        showNotification: function(message, type = 'info') {
            const icons = {
                success: '<svg width="20" height="20" fill="#28a745" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
                error: '<svg width="20" height="20" fill="#dc3545" viewBox="0 0 24 24"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>',
                warning: '<svg width="20" height="20" fill="#ffc107" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
                info: '<svg width="20" height="20" fill="#17a2b8" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>'
            };
            
            const notification = `
                <div class="notification ${type}">
                    <div class="notification-icon">${icons[type] || icons.info}</div>
                    <span class="notification-message">${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            `;
            
            if (!$('.notifications-container').length) {
                $('body').append('<div class="notifications-container"></div>');
            }
            
            const $notification = $(notification);
            $('.notifications-container').append($notification);
            
            setTimeout(() => $notification.addClass('notification-show'), 10);
            
            // Auto remove
            setTimeout(() => {
                $notification.removeClass('notification-show').addClass('notification-hide');
                setTimeout(() => $notification.remove(), 300);
            }, 5000);
            
            // Manual close
            $notification.find('.notification-close').on('click', function() {
                $notification.removeClass('notification-show').addClass('notification-hide');
                setTimeout(() => $notification.remove(), 300);
            });
        },

        saveBookingData: function(data) {
            return new Promise((resolve, reject) => {
                // Check if cinema_ajax is available
                if (typeof cinema_ajax === 'undefined') {
                    console.warn('cinema_ajax not available, using localStorage backup');
                    localStorage.setItem('cinema_booking_data', JSON.stringify(data));
                    resolve({ success: true });
                    return;
                }

                $.ajax({
                    url: cinema_ajax.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'cinema_seat_selection',
                        nonce: cinema_ajax.nonce,
                        ...data
                    },
                    timeout: 10000,
                    success: function(response) {
                        if (response && response.success) {
                            resolve(response.data);
                        } else {
                            reject(response ? response.data : 'Unknown error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        reject(error);
                    }
                });
            });
        }
    };

    $(document).ready(function() {
        SeatSelection.init();
    });

})(jQuery);