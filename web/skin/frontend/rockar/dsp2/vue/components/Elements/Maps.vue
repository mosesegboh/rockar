<script>
    import numeral from 'numeral';
    import coreMaps from 'core/components/Elements/Maps';

    export default coreMaps.extend({
        data: () => ({
            googleApiLimit: 25,
            distanceFrame: 0,
            distanceCalcRetries: 3,
            distanceRetryDelay: 100, // in ms
            chunkStatus: {
                OK: 'OK',
                FAILED: 'FAILED'
            }
        }),

        methods: {
            /**
             * Calculate distances for all locations
             * @async
             * @return {Boolean} successful?
             */
            calculateDistances() {
                /* store calculation start time in function and app
                 * to detect if another call to calculateDistances
                 * has been made more recently
                 */
                const frame = this.distanceFrame = Date.now();

                // Split locations in chunks to process
                const chunks = this.locations.reduce((acc, item, index) => {
                    const chunkIndex = Math.floor(index / this.googleApiLimit),
                        chunk = acc[chunkIndex] || [];

                    if (!acc[chunkIndex]) {
                        acc[chunkIndex] = chunk;
                    }

                    chunk.push(item);

                    return acc;
                }, []);

                // Handle all chunks simultaneously
                return Promise.all(chunks.map(chunk => this.processChunk(
                    chunk,
                    this.distanceRetryDelay,
                    this.distanceCalcRetries
                )))
                .then(results => {
                    // Reject results if a more recent calculation has been initiated
                    if (frame !== this.distanceFrame) {
                        return Promise.reject();
                    }

                    // filter out results that did not fail
                    results = results.filter(result => result.status === this.chunkStatus.OK)
                        .map(result => result.value);

                    // Reassign chunks for locations when all are calculated
                    this.locations = results.reduce((acc, chunk) => {
                        acc.push(...chunk);

                        return acc;
                    }, []);

                    this.distancesCalculated = true;
                    this.ajaxLoading = false;
                    this.$emit('update-stores-data');

                    return true;
                });
            },

            /**
             * Calculate distance for a chunk of locations
             * @async
             * @param {Object[]} chunk
             * @return {Object[]} chunk
             */
            calculateChunkDistance(chunk) {
                return this.getDistanceMatrix({
                    destinations: chunk.map(item => new this.google.maps.LatLng(item.location.lat, item.location.lng)),
                    origins: [
                        new this.google.maps.LatLng(this.userLocation[0], this.userLocation[1])
                    ],
                    travelMode: this.typeOfTravel,
                    unitSystem: this.google.maps.UnitSystem.METRIC
                })
                .catch(status => {
                    const errorMessage = `Failed to calculate distances for the following reason: ${status}.`;
                    console.error(errorMessage);
                    this.errors = errorMessage;

                    return Promise.reject(chunk);
                })
                .then(response => {
                    const elements = response.rows[0].elements;

                    return chunk.map((item, index) => {
                        const element = elements[index];

                        if (element && element.status === 'OK') {
                            item.distance = Number(element.distance.value);
                            item.distanceFormatted = element.distance.text;
                        }

                        return item;
                    })
                });
            },

            /**
             * Wrapper function for google api getDistance Matrix
             * @async
             * @param {Object} options
             * @return {Object} response
             */
            getDistanceMatrix(options) {
                return new Promise((resolve, reject) => {
                    this.distanceMatrixService.getDistanceMatrix(
                        options,
                        (response, status) => {
                            if (status === 'OK') {
                                resolve(response);
                            }

                            reject(status);
                        }
                    )
                });
            },

            /**
             * Process one chunk and retry a number of times if it fails for some reason
             *
             * @param {Array} chunk
             * @param {Number} delay
             * @param {Number} times
             * @returns {Promise}
             */
            processChunk(chunk, delay, times) {
                const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

                return new Promise((resolve, reject) => this.calculateChunkDistance(chunk)
                    .then(value => {
                        resolve(
                            {
                                status: this.chunkStatus.OK,
                                value
                            })
                    })
                    .catch(reason => {
                        if (times - 1 > 0) {
                            return wait(delay)
                                .then(this.processChunk.bind(null, chunk, delay, times - 1))
                                .then(resolve);
                        }

                        return resolve({
                            status: this.chunkStatus.FAILED,
                            value: chunk
                        });
                    })
                );
            },

            /**
             * Add stores to the map. Visual adjustment comparing to core
             */
            addStores() {
                if (this.locations.length) {
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

                        marker.addListener('mouseover', () => {
                            this.closeInfowindows();
                            infoWindow.open(this.map, marker);
                        });

                        marker.addListener('click', () => {
                            this.centerMapOnMarker(marker);
                            this.$dispatch('Delivery::setCurrentStore', marker.store);
                            this.lastMarker = marker;
                        });

                        if (index === 0) {
                            this.lastMarker = marker;
                            infoWindow.open(this.map, marker);
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
                }
            }
        }
    });
</script>