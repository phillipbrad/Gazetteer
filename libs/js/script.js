var lat;
var lng;
var countryLat = 0;
var countryLng = 0;
var iso_a2;
var countryNames = {};
//var capital = '';
//var bounds = { south: 0, west: 0, east: 0, north: 0 };

$(window).on("load", function () {
    $("#appContent").hide();
});

// Function to hide preloader after adding layers
function hidePreloader() {
    $("#preloader").fadeOut("slow", function () {
        $("#appContent").fadeIn("slow");
    });
}

// tile layers
var streets = L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}", {
    attribution: "Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012"
});

var satellite = L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", {
    attribution: "Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community"
});

var basemaps = {
    "Streets": streets,
    "Satellite": satellite,
};

var map = L.map('map', {
    layers: [streets],
    minZoom: 2.5
}).setView([54.5, -4], 6);

//Create the icons 
var airportIcon = L.ExtraMarkers.icon({
    prefix: "fa",
    icon: "fa-plane",
    iconColor: "red",
    markerColor: "black",
    shape: "square"
});

var hospitalIcon = L.ExtraMarkers.icon({
    prefix: "fa",
    icon: "fa-hospital",
    iconColor: "red",
    markerColor: "white",
    shape: "square"
});

var cityIcon = L.ExtraMarkers.icon({
    prefix: "fa",
    icon: "fa-city",
    iconColor: "",
    markerColor: "blue",
    shape: "square"
});

var archaeologicalIcon = L.ExtraMarkers.icon({
    prefix: "fa",
    icon: "fa-archway",
    iconColor: "",
    markerColor: "orange",
    shape: "square"
});

var railwayIcon = L.ExtraMarkers.icon({
    prefix: "fa",
    icon: "fa-train",
    iconColor: "black",
    markerColor: "green",
    shape: "square"
})

//Create layer groups
var airportLayerGroup = L.layerGroup();
var cityLayerGroup = L.layerGroup();
var archaeologicalLayerGroup = L.layerGroup();
var hospitalLayerGroup = L.layerGroup();
var railwayLayerGroup = L.layerGroup();
//create cluster groups
var airportClusterGroup = L.markerClusterGroup({
    polygonOptions: {
        fillColor: "#383534",
        color: "#e31b05",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.5
    }
});
var hospitalClusterGroup = L.markerClusterGroup({
    polygonOptions: {
        fillColor: "#f7240c",
        color: "white",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.5
    }
});
var archaeologicalClusterGroup = L.markerClusterGroup({
    polygonOptions: {
        fillColor: "orange",
        color: "white",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.5
    }
});
var cityClusterGroup = L.markerClusterGroup({
    polygonOptions: {
        fillColor: "blue",
        color: "black",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.5
    }
});

var railwayClusterGroup = L.markerClusterGroup({
    polygonOptions: {
        fillColor: "green",
        color: "black",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.5
    }
});
//add clusterGroups to layerGroups
airportLayerGroup.addLayer(airportClusterGroup);
cityLayerGroup.addLayer(cityClusterGroup);
archaeologicalLayerGroup.addLayer(archaeologicalClusterGroup);
hospitalLayerGroup.addLayer(hospitalClusterGroup);
railwayLayerGroup.addLayer(railwayClusterGroup);

// Add layer control to the map
var overlays = {
    "Airports": airportLayerGroup,
    "Cities": cityLayerGroup,
    "Archaeological Sites": archaeologicalLayerGroup,
    "Hospitals": hospitalLayerGroup,
    "Railways": railwayLayerGroup
};


// Cluster Group initialization
let markerClusterGroup = L.markerClusterGroup({
    maxClusterRadius: 80,
    showCoverageOnHover: false,
    spiderfyOnMaxZoom: true,
    zoomToBoundsOnClick: true,
});

//Create layer control allows users to switch between different map layers
layerControl = L.control.layers(basemaps, overlays).addTo(map);
//Create the weather button
var weatherBtn = L.easyButton({
    states: [{
        icon: '<img src="libs/icons/temperature-half-solid.png" style="width: 20px; height: 20px;">',
        title: "Show Weather",
        onClick: function () {
            $("#weatherModal").modal("show");
        }
    }]
}).addTo(map);

var wikiInfoBtn = L.easyButton({
    states: [{
        icon: '<img src="libs/icons/wikipedia-w-brands.png" style="width: 20px; height: 20px;">',
        title: "Show Weather",
        onClick: function () {
            $("#wikipediaModal").modal("show");
        }
    }]
}).addTo(map);

var newsBtn = L.easyButton({
    states: [{
        icon: '<img src="libs/icons/newspaper-solid.png" style="width: 20px; height: 20px;">',
        title: "Show News",
        onClick: function () {
            $("#newsModal").modal("show");
        }
    }]
}).addTo(map);

var countryInfoBtn = L.easyButton({
    states: [{
        icon: '<img src="libs/icons/circle-info-solid.png" style="width: 20px; height: 20px;">',
        title: "Show News",
        onClick: function () {
            $("#countryInfoModal").modal("show");
        }
    }]
}).addTo(map);

var currencyBtn = L.easyButton({
    states: [{
        icon: '<img src="libs/icons/money-bill-solid.png" style="width: 20px; height: 20px;">',
        title: "Show currency",
        onClick: function () {
            $("#currencyModal").modal("show");
        }
    }]
}).addTo(map);


L.control.scale().addTo(map);

// Populate the select element 
$.ajax({
    url: 'libs/php/getCountries.php',
    method: 'GET',
    dataType: 'json',
    success: function (data) {
        var select = $('#countrySelect');
        select.empty();

        // Extract and sort the countries alphabetically
        var countries = data.features.map(function (country) {
            return {
                iso_a2: country.iso_a2,
                name: country.name
            };
        }).sort(function (a, b) {
            return a.name.localeCompare(b.name);
        });

        // Populate the select element with sorted countries
        $.each(countries, function (index, country) { //Loops through the countries in the countries array
            countryNames[country.iso_a2] = country.name;
            select.append($('<option>', {
                value: country.iso_a2,
                text: country.name
            }));
        });
    },
    error: function (jqXHR, textStatus, errorThrown) {
        console.error('Error fetching country data:', textStatus, errorThrown);
    }
});

function getCountryBorder(iso_a2) {

    $("#preloader").show();
    $("#appContent").hide();

    if (map.borderLayer) {
        map.removeLayer(map.borderLayer);
    }

    if (map.markers) {
        map.markers.forEach(marker => map.removeLayer(marker));
    }

    

    // Fetch the country border for all countries
    $.ajax({
        url: 'libs/php/getCountryBorder.php',
        method: 'GET',
        data: { iso_a2: iso_a2 },
        dataType: 'json',
        success: function (data) {

            map.borderLayer = L.geoJSON(data, {
                style: {
                    color: "#361999",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.1
                }
            }).addTo(map);

            // Get bounds from the border layer
            let bounds = map.borderLayer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds);

                // Use the fetched bounds for non-hardcoded countries
                if (!countryLat || !countryLng) {
                    let center = bounds.getCenter();
                    countryLat = center.lat;
                    countryLng = center.lng;
                    
                }
            } else {
                console.error('Bounds are not valid:', bounds);
            }

            // Call functions and collect promises
            const promises = [
                getWeatherData(countryLat, countryLng),
                getArchaeologicalSite(iso_a2),
                getCities(iso_a2),
                getHospitals(iso_a2),
                getAirports(iso_a2),
                getRailways(iso_a2)
            ];

            // Wait for all promises to resolve
            Promise.all(promises).then(() => {
                // Toggle overlays on
                map.addLayer(airportLayerGroup);
                map.addLayer(cityLayerGroup);
                map.addLayer(archaeologicalLayerGroup);
                map.addLayer(hospitalLayerGroup);
                map.addLayer(railwayLayerGroup);


                hidePreloader();
            }).catch(error => {
                console.error('Error loading layers:', error);
                hidePreloader();
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching country border data:', textStatus, errorThrown);
            hidePreloader();
        }
    });
}

$('#countrySelect').change(function () {
    var iso_a2 = $(this).val();
    var countryName = countryNames[iso_a2];
    getCountryBorder(iso_a2);


    $('#weather-model-country').text(countryName + ' Weather');
    getNews(countryName);
    getWikipediaInfo(countryName);
    getCountryInfo(iso_a2);

});


function getWeatherData(lat, lng) {
    
    return $.ajax({
        url: 'libs/php/getWeather.php',
        type: 'GET',
        dataType: 'json',
        data: {
            lat: lat,
            lng: lng
        },
        success: function (response) {
            const details = response.current;

            // Current weather data
            const iconCode = details.weather[0].icon;
            const iconUrl = `http://openweathermap.org/img/wn/${iconCode}@2x.png`;

            // Populate current weather section
            $('#currentWeatherIcon').attr('src', iconUrl);
            $('#currentTemp').html('<strong>' + numeral(details.temp).format('0') + "°C" + '</strong>');
            $('#currentDescription').html('<strong>' + details.weather[0].description + '</strong>');

            // 3 day forecast data
            const forecast = response.daily;
            $('#forecastContainer').empty();

            // Iterate through the forecast data
            for (let i = 1; i <= 3; i++) {
                const forecastDay = forecast[i];
                const forecastIconUrl = `http://openweathermap.org/img/wn/${forecastDay.weather[0].icon}@2x.png`;
                const date = new Date(forecastDay.dt * 1000).toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });


                const forecastHTML = `
                    <div class="col forecast-day">
                        <p><strong>${date}</strong></p>
                        <img src="${forecastIconUrl}" alt="Weather Icon">
                        <p><strong>${Math.round(forecastDay.temp.min)}°C / ${Math.round(forecastDay.temp.max)}°C</strong></p>
                    </div>
                `;

                $('#forecastContainer').append(forecastHTML);
            }

            // Hide preloader and show content
            setTimeout(function () {
                $('#weatherPreloader').hide();
                $('#currentWeatherContainer').show();
                $('#forecastContainer').show();
            }, 2000);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching weather data:', textStatus, errorThrown);
            setTimeout(function () {
                $('#weatherPreloader').hide();
                alert('Failed to load weather data');
            }, 2000);
        }
    });
}


function getCountryInfo(iso_a2) {

    $.ajax({
        url: 'libs/php/getCountryInfo.php',
        method: 'GET',
        data: { iso2: iso_a2 },
        dataType: 'json',
        success: function (response) {
            console.log('Country info response:', response);

            // Check if response has the expected structure
            if (!response.geonames || !response.geonames[0]) {
                console.error('Invalid country info response structure:', response);
                $('#countryNameData').text('N/A');
                $('#capitalData').text('N/A');
                $('#continentData').text('N/A');
                $('#populationData').text('N/A');
                $('#currencyData').text('N/A');
                $('#areaData').text('N/A');
                $('#languagesData').text('N/A');
                return;
            }

            var countryData = response.geonames[0];
            var geonameId = response.geonameId;
            var population = countryData.population ? numeral(countryData.population).format('0,0') : 'N/A';
            var languages = countryData.languages ? countryData.languages : 'N/A';
            var currency = countryData.currencyCode ? countryData.currencyCode : 'N/A';
            var area = countryData.areaInSqKm ? countryData.areaInSqKm : 'N/A';
            var continent = countryData.continentName ? countryData.continentName : 'N/A';

            // Update each modal field with the corresponding data
            $('#countryNameData').text(countryData.countryName || 'N/A');
            $('#capitalData').text(countryData.capital || 'N/A');
            $('#continentData').text(continent);
            $('#populationData').text(population);
            $('#currencyData').text(currency);
            $('#areaData').text(numeral(area).format('0,0') + ' km²');
            $('#languagesData').text(languages);
            $('#weatherModalLabel').text(countryData.capital)

            getCurrencyConversion(currency);

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching country info:', textStatus, errorThrown);
        }
    });
};

function getNews(countryName) {

    $.ajax({
        url: 'libs/php/getNews.php',
        method: 'GET',
        data: { countryName: countryName },
        dataType: 'json',
        success: function (response) {

            if (response && response.articles && response.articles.length > 0) {
                let newsHtml = '';

                // Get onlythe first 4 articles
                response.articles.slice(0, 4).forEach(article => {
                    let imageUrl = article.urlToImage
                        ? article.urlToImage
                        : 'libs/icons/new_placeholder.png'; // Default image if none exists

                    newsHtml += `
                        <table class="table table-borderless mb-3">
                            <tr>
                                <td rowspan="2" width="40%">
                                    <img class="img-fluid rounded" src="${imageUrl}" alt="News Image">
                                </td>
                                <td>
                                    <a href="${article.url}" class="fw-bold fs-6 text-black" target="_blank">
                                        ${article.title}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-bottom pb-0">
                                    <p class="fw-light fs-6 mb-1">${article.source.name ? article.source.name : "Unknown Source"}</p>
                                </td>
                            </tr>
                        </table>
                        <hr>
                    `;
                });

                $('#newsContent').html(newsHtml);

            } else {
                $('#newsContent').html('<p class="text-center">No news available.</p>');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching news:', textStatus, errorThrown);
            $('#newsContent').html('<p class="text-center">Error loading news.</p>');
        }
    });
}


function getRailways(iso_a2) {

    return $.ajax({
        url: 'libs/php/getRailways.php',
        method: 'GET',
        data: { iso_a2: iso_a2 },
        dataType: 'json',
        success: function (response) {

            if (response.error) {
                console.error('Error:', response.error);
                alert(response.error);
            } else {

                railwayClusterGroup.clearLayers();

                response.features.forEach(function (station) {
                    var lat = station.geometry.coordinates[1];
                    var lon = station.geometry.coordinates[0];
                    var name = (station.properties.name || 'Unnamed') + ' Station';  // Append Station to the name


                    var marker = L.marker([lat, lon], { icon: railwayIcon })
                        .bindPopup('<b>' + name + '</b>');

                    railwayClusterGroup.addLayer(marker);
                });


                railwayClusterGroup.addTo(map);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);

        }
    });
}



function getArchaeologicalSite(iso_a2) {

    return $.ajax({
        url: 'libs/php/getArchaeologicalSite.php',
        method: 'GET',
        data: {
            iso_a2: iso_a2
        },
        dataType: 'json',
        success: function (response) {


            if (response.error) {
                console.error('Error fetching archaeological data:', response.error);
                return;
            }

            archaeologicalClusterGroup.clearLayers();

            response.features.forEach(info => {
                if (!info.geometry || !info.geometry.coordinates || info.geometry.coordinates.length < 2) {
                    console.warn("Invalid archaeological coordinates:", info);
                    return;
                }
                const [lng, lat] = info.geometry.coordinates;

                const marker = L.marker([lat, lng], { icon: archaeologicalIcon })
                    .bindPopup(`<b>${info.properties.name}</b><br>${info.properties.description}`);
                archaeologicalClusterGroup.addLayer(marker);
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching Wikipedia data:', textStatus, errorThrown);


            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                console.error('archaeological API Error:', jqXHR.responseJSON.error);
            } else {
                console.error('Unknown error occurred while fetching archaeological data.');
            }
        }
    });
}

function getWikipediaInfo(countryName) {
    var countryName = countryName;
    $.ajax({
        url: 'libs/php/getWikipediaInfo.php',
        method: 'GET',
        data: { countryName: countryName },
        dataType: 'json',
        success: function (response) {

            $('#wikipediaSummaryData').html(`
                <h5 class="text-primary">${response.title}</h5>
                <p>${response.extract}</p>
            `);

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error fetching wiki data:", textStatus, errorThrown);
            console.error("Response text:", jqXHR.responseText);
        }
    });
}

function getCities(iso_a2) {

    return $.ajax({
        url: 'libs/php/getCities.php',
        method: 'GET',
        data: { iso_a2: iso_a2 },
        dataType: 'json',
        success: function (response) {


            if (response.error) {
                console.error("Server Error:", response.error);
                return;
            }

            cityClusterGroup.clearLayers();

            if (Array.isArray(response.features) && response.features.length > 0) {
                response.features.forEach(feature => {
                    const props = feature.properties;
                    const [lng, lat] = feature.geometry.coordinates;

                    const marker = L.marker([lat, lng], { icon: cityIcon }) 
                    .bindPopup(`<b>${props.name}</b><br>Population: ${numeral(props.population).format('0,0')}<br>Country: ${props.country}`);

                    cityClusterGroup.addLayer(marker);
                });
            } else {
                console.warn('No cities found.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

function getHospitals(iso_a2) {

    return $.ajax({
        url: 'libs/php/getHospitals.php',
        method: 'GET',
        data: { iso_a2: iso_a2 },
        dataType: 'json',
        success: function (response) {


            if (!response.features || !Array.isArray(response.features)) {
                console.error('Invalid response format:', response);
                return;
            }
            hospitalClusterGroup.clearLayers();

            response.features.forEach(feature => {
                let coords = feature.geometry.coordinates;
                let name = feature.properties.name;

                if (!coords || coords.length < 2) {
                    console.error("Invalid hospital coordinates:", feature);
                    return;
                }

                const marker = L.marker([coords[1], coords[0]], { icon: hospitalIcon })
                    .bindPopup(`<b>${name}</b>`);
                hospitalClusterGroup.addLayer(marker);
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching Hospitals data:', textStatus, errorThrown);
            console.error('Response text:', jqXHR.responseText);
        }
    });
}


function getAirports(iso_a2) {

    return $.ajax({
        url: 'libs/php/getAirports.php',
        method: 'GET',
        data: { iso_a2 },
        dataType: 'json',
        success: function (response) {


            if (response.error) {
                console.warn("No airports found:", response.error);
                return;
            }

            airportClusterGroup.clearLayers();

            response.features.forEach(feature => {
                const props = feature.properties;
                const [lng, lat] = feature.geometry.coordinates;

                const marker = L.marker([lat, lng], { icon: airportIcon })
                    .bindPopup(`<b>${props.name}</b><br>${props.city}, ${props.country}`);

                airportClusterGroup.addLayer(marker);
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
}


function getCurrencyConversion(currency) {
    console.log('getCurrencyConversion called with currency:', currency);

    $.ajax({
        url: 'libs/php/getCurrencyConversion.php',
        method: 'GET',
        data: { currency: currency },
        dataType: 'json',
        success: function (response) {
            console.log('Currency API response:', response);

            if (response.status === "success") {
                let modalBody = $("#currencyModalBody");
                modalBody.empty();

                let baseCurrency = response.base_currency_name;
                let baseCode = response.base_currency_code;
                let updatedDate = response.updated_date;
                let defaultAmount = parseFloat(response.amount);
                let rates = response.rates;

                let html = `
                    <div class="text-center mb-3">
                        <h5 class="fw-bold text-primary">${baseCurrency} (${baseCode})</h5>
                        <p class="text-muted">Exchange rates updated: ${updatedDate}</p>
                        <hr>
                    </div>
                    <div class="mb-3 text-center">
                        <label for="currencyAmount" class="form-label fw-bold">Enter Amount:</label>
                        <input type="number" id="currencyAmount" class="form-control text-center w-50 mx-auto" 
                               value="${defaultAmount}" min="0" step="0.01">
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 g-3" id="exchangeRates">
                `;

                $.each(rates, function (code, rateData) {
                    let rate = parseFloat(rateData.rate);
                    let rateColor = rate > 1 ? "success" : "danger";

                    html += `
                        <div class="col">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <img src="https://flagcdn.com/w40/${code.substring(0, 2).toLowerCase()}.png" 
                                         onerror="this.onerror=null; this.src='libs/icons/default_flag.png';" 
                                         class="mb-2" alt="${rateData.currency_name}" width="40">
                                    <h6 class="card-title fw-bold">${rateData.currency_name} (${code})</h6>
                                    <span class="badge bg-${rateColor} fs-6 converted-amount" 
                                          data-rate="${rate}">
                                            ${numeral(defaultAmount * rate).format('0,0.00')} 
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div>`;

                modalBody.html(html);
                $("#currencyModalLabel").text(`Currency Conversion: ${baseCurrency} (${baseCode})`);


                $("#currencyAmount").on("input", function () {
                    let newAmount = parseFloat($(this).val()) || 0;
                    $(".converted-amount").each(function () {
                        let rate = parseFloat($(this).data("rate"));
                        $(this).text((newAmount * rate).toFixed(4));
                    });
                });

            } else {
                console.error("Invalid response - no status=success:", response);
                $("#currencyModalBody").html(`<p class="text-danger">Unable to fetch exchange rates. Check console for details.</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response text:", xhr.responseText);
            $("#currencyModalBody").html(`<p class="text-danger">Error fetching currency data: ${status}</p>`);
        }
    });
}



var weatherIcon = 'libs/icons/temperature-half-solid.png';


var WeatherControl = L.Control.extend({
    onAdd: function (map) {
        var div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');//creates an html element
        div.style.backgroundImage = 'url(' + weatherIcon + ')';
        div.style.backgroundSize = '30px 30px';
        div.style.width = '30px';
        div.style.height = '30px';
        div.title = 'Weather Information';
        div.onclick = function () {
            $('#weatherModal').modal('show');
        };
        return div;
    }
})

function userLocation() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                lat = position.coords.latitude;
                lng = position.coords.longitude;

                $.ajax({
                    url: 'libs/php/getLocation.php',
                    method: 'GET',
                    data: {
                        latitude: lat,
                        longitude: lng
                    },
                    dataType: 'json',
                    success: function (result) {

                        if (result && result.latitude && result.longitude) {
                            const countryCode = result.iso2;
                            const countryName = result.location;


                            $('#countrySelect').val(countryCode).trigger('change');


                            // Center the map on the user's location
                            map.setView([result.latitude, result.longitude], 6);
                        } else {
                            console.error('Invalid location data:', result);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error fetching location data:', textStatus, errorThrown);
                    }
                });
            },
            (error) => {
                console.error("Error getting user location:", error);
                // If no permissions are given, default to view over the UK
                map.setView([54.5, -4], 6);
            }
        );
    } else {
        console.error("Geolocation is not supported by this browser.");
        map.setView([54.5, -4], 6); // Center the map over the UK
    }
};

$(window).on('load', function () {
    $(".loader-wrapper").fadeOut("slow");
    userLocation();
    $(".modal").modal("hide");
});

