<script>
    import appMaps from 'mini/components/Elements/Maps';

    export default appMaps.extend({
        props: {
            centredOnUser: {
                required: false,
                type: Boolean,
                default: () => false
            },

            markerIcon: {
                required: false,
                type: String,
                default: '/skin/frontend/rockar/mini/images/map/map-marker.png'
            },

            activeMarkerIcon: {
                required: false,
                type: String,
                default: '/skin/frontend/rockar/mini/images/map/map-marker-active.png'
            },

            userMarkerIcon: {
                required: false,
                type: String,
                default: '/skin/frontend/rockar/mini/images/map/map-marker-user.png'
            }
        },

        data() {
            return {
                defaultUserCoordinates: [-29.116667, 26.216667], // those are Bloemfontein coordinates
                storeSelected: false,
                minZoom: 6
            };
        },

        methods: {
            redrawMap(updateDistance = false) {
                if (this.$parent.dealerStateStep !== 2) {
                    return;
                }

                if (updateDistance) {
                    this.calculateDistances();

                    if (this.markers.length) {
                        this.updateStores();
                    }
                }

                if (this.markers.length && this.distancesCalculated) {
                    this.closeInfowindows();
                    this.resetMarkerIcons();

                    this.markers.forEach((marker) => {
                        if (this.lastMarker.store && parseInt(marker.store.id) === parseInt(this.lastMarker.store.id)) {
                            this.lastMarker.infoWindow.open(this.map, this.lastMarker);
                            this.lastMarker.setIcon(this.activeMarkerIcon);
                            this.lastMarker.setMap(this.map);
                        }
                    });

                    setTimeout(() => {
                        const marker = !this.storeSelected && this.centredOnUser ? this.userMarker : this.lastMarker;

                        this.centerMapOnMarker(marker);
                    }, 300);
                }
            },

            addStores() {
                if (this.locations.length && this.jsApiLoaded) {
                    const firstLocation = this.locations[0];
                    const userLocation = new this.google.maps.LatLng(this.userLocation[0], this.userLocation[1]);

                    this.map.setCenter(firstLocation.location);

                    this.bounds = new google.maps.LatLngBounds();
                    this.locations.forEach((location, index) => {
                        let contentString = `<strong>${location.title}</strong>`;

                        if (!!location.tooltipInfo) {
                            contentString += `<p>${location.tooltipInfo}</p>`;
                        }

                        const infoWindow = new google.maps.InfoWindow({
                            content: contentString,
                            maxWidth: this.maxInfoWindowWidth,
                            disableAutoPan: true
                        });

                        const marker = new google.maps.Marker({
                            position: location.location,
                            map: this.map,
                            title: 'Pick the store',
                            store: location,
                            icon: this.markerIcon,
                            infoWindow
                        });

                        this.infoBoxes.push(infoWindow);

                        google.maps.event.addListener(infoWindow, 'domready', () => {
                            this.centerMapOnMarker(marker);
                        });

                        marker.addListener('click', () => {
                            this.lastMarker = marker;
                            this.selectStoreMarker(location.id);
                        });

                        if (!this.lastMarker.store && index === 0) {
                            this.lastMarker = marker;
                            this.selectStoreMarker(location.id);
                        }

                        this.markers.push(marker);
                        this.bounds.extend(marker.getPosition());
                    });

                    this.userMarker = new google.maps.Marker({
                        position: userLocation,
                        map: this.map,
                        title: 'Your location',
                        icon: this.userMarkerIcon
                    });

                    this.bounds.extend(this.userMarker.getPosition());

                    if (this.locations.length > 0) {
                        this.map.fitBounds(this.bounds);
                    } else {
                        this.map.setZoom(this.zoom);
                    }
                }
            },

            resetMarkerIcons() {
                if (this.markers.length) {
                    this.markers.forEach((el) => {
                        el.setIcon(this.markerIcon);
                    });
                }
            },

            centerMapOnMarker(marker) {
                const scale = 1 << this.map.getZoom();
                const infoBoxContainer = jQuery('.gm-style .gm-style-iw');
                let latlng = marker.position;
                let worldCoordinate;

                if (infoBoxContainer) {
                    const offset = infoBoxContainer.parent().height() / 2;
                    worldCoordinate = this.map.getProjection().fromLatLngToPoint(latlng);
                    const offsetCoordinate = new google.maps.Point(
                        worldCoordinate.x,
                        (worldCoordinate.y * scale - offset) / scale
                    );
                    latlng = this.map.getProjection().fromPointToLatLng(offsetCoordinate);
                }

                const offsetX = (document.getElementById('map').getBoundingClientRect().width) / 4;
                worldCoordinate = this.map.getProjection().fromLatLngToPoint(latlng);
                const offsetXCoordinate = new google.maps.Point(
                    (worldCoordinate.x * scale - offsetX) / scale,
                    worldCoordinate.y
                );
                latlng = this.map.getProjection().fromPointToLatLng(offsetXCoordinate);

                this.map.panTo(latlng);
            },

            initMap() {
                this.$super(appMaps, 'initMap');

                if (this.jsApiLoaded) {
                    this.map.setOptions({ minZoom: this.minZoom });
                }
            },

            selectStoreMarker(storeId) {
                if (!this.distancesCalculated) {
                    return;
                }

                storeId = parseInt(storeId) || 0;

                this.storeSelected = false;
                this.markers.forEach((marker) => {
                    if (parseInt(marker.store.id) === storeId) {
                        this.lastMarker = marker;
                        this.storeSelected = true;

                        this.$parent.selectDealerById(storeId);
                        this.redrawMap();
                    }
                });
            }
        },

        events: {
            'Map::selectStoreMarker'(storeId) {
                this.selectStoreMarker(storeId);
            },

            // Map re-draw in case you change the views/pages
            'Map::reload'(storeId, recalculateDistances = false) {
                setTimeout(() => { // refresh DOM before reload
                    jQuery('img[src*="map-marker-active"]').remove();

                    if (recalculateDistances) {
                        this.distancesCalculated = false;
                        this.currentUserLocation();
                    }

                    if (!this.jsApiLoaded && this.geocoder) {
                        this.initMap();
                    }

                    if (this.jsApiLoaded) {
                        this.map = new google.maps.Map(document.getElementById('map'), {
                            zoom: this.zoom,
                            center: {
                                lat: this.userLocation[0],
                                lng: this.userLocation[1]
                            },
                            minZoom: 3,
                            disableDefaultUI: true,
                            zoomControl: true
                        });
                    }

                    this.markers.forEach(marker => {
                        if (marker.store.id === storeId) {
                            this.lastMarker = marker;
                        }
                    });

                    if (!recalculateDistances && this.jsApiLoaded) {
                        // Triggers redrawMap
                        google.maps.event.trigger(this.map, 'resize');
                    }

                    jQuery('.dealer-list').scrollTop(0);
                }, 1);
            }
        },

        created() {
            if (this.$options.events['Map::reload'].length > 1) {
                // Remove default event Map::reload if in Test Drive to reduce flickering between selected markers on dealerStateStep 2
                this.$options.events['Map::reload'].splice(0, 1);
            }
        }
    })
</script>
