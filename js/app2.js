const $ = id => document.getElementById(id);

const form = $("formulario-clima");
const input = $("buscar-ciudad");
const btn = $("btn-buscar");
const carga = $("indicador-carga");
const error = $("mensaje-error");
const resultado = $("contenedor-clima");

function escribir(id, valor) {
    const elemento = document.getElementById(id);

    if (elemento) {
        elemento.textContent = valor;
    }
}

function asignarHref(id, valor) {
    const elemento = document.getElementById(id);

    if (elemento) {
        elemento.href = valor;
    }
}

function mostrarError(mensaje) {
    if (error) {
        error.textContent = mensaje;
        error.classList.remove("hidden");
    }
}

function limpiarError() {
    if (error) {
        error.textContent = "";
        error.classList.add("hidden");
    }
}

function climaTexto(codigo) {
    if (codigo === 0) return "Despejado";
    if ([1, 2, 3].includes(codigo)) return "Parcialmente nublado";
    if ([45, 48].includes(codigo)) return "Niebla";
    if ([51, 53, 55].includes(codigo)) return "Llovizna";
    if ([61, 63, 65, 80, 81, 82].includes(codigo)) return "Lluvia";
    if ([71, 73, 75, 77, 85, 86].includes(codigo)) return "Nieve";
    if ([95, 96, 99].includes(codigo)) return "Tormenta";

    return "Condiciones variables";
}

function climaIcono(codigo) {
    if (codigo === 0) return "☀️";
    if ([1, 2, 3].includes(codigo)) return "⛅";
    if ([45, 48].includes(codigo)) return "🌫️";
    if ([51, 53, 55, 61, 63, 65, 80, 81, 82].includes(codigo)) return "🌧️";
    if ([71, 73, 75, 77, 85, 86].includes(codigo)) return "❄️";
    if ([95, 96, 99].includes(codigo)) return "⛈️";

    return "🌍";
}

async function obtenerJson(url, mensajeError) {
    const respuesta = await fetch(url);

    if (!respuesta.ok) {
        throw new Error(mensajeError);
    }

    return await respuesta.json();
}

function formatearHoraDesdeISO(iso, zonaHoraria) {
    if (!iso) return "No disponible";

    return new Intl.DateTimeFormat("es-EC", {
        hour: "2-digit",
        minute: "2-digit",
        timeZone: zonaHoraria || "UTC"
    }).format(new Date(iso));
}

async function buscar(ciudad) {
    if (carga) carga.classList.remove("hidden");
    if (resultado) resultado.classList.add("hidden");
    limpiarError();

    if (btn) btn.disabled = true;

    try {
        // API 1: Open-Meteo Geocoding
        const geocoding = await obtenerJson(
            `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(ciudad)}&count=1&language=es&format=json`,
            "No se pudo encontrar la ciudad."
        );

        if (!geocoding.results || geocoding.results.length === 0) {
            throw new Error("Ciudad no encontrada.");
        }

        const ubicacion = geocoding.results[0];
        const latitud = ubicacion.latitude;
        const longitud = ubicacion.longitude;
        const zonaHoraria = ubicacion.timezone || "UTC";

        // API 1: Open-Meteo Forecast
        const clima = await obtenerJson(
            `https://api.open-meteo.com/v1/forecast?latitude=${latitud}&longitude=${longitud}&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min&timezone=auto&forecast_days=1`,
            "No se pudo consultar el clima."
        );

        // API 2: Sunrise-Sunset
        const sol = await obtenerJson(
            `https://api.sunrise-sunset.org/json?lat=${latitud}&lng=${longitud}&formatted=0`,
            "No se pudo consultar el amanecer y atardecer."
        );

        // API 3: Wikipedia
        let wiki = null;

        try {
            wiki = await obtenerJson(
                `https://es.wikipedia.org/api/rest_v1/page/summary/${encodeURIComponent(ubicacion.name)}`,
                "No se pudo consultar Wikipedia."
            );
        } catch (e) {
            wiki = {
                extract: "No se encontró una descripción disponible en Wikipedia para esta ciudad.",
                content_urls: {
                    desktop: {
                        page: "https://es.wikipedia.org"
                    }
                }
            };
        }

        // API 4: TimeAPI.io
        let tiempoLocal = null;

        try {
            tiempoLocal = await obtenerJson(
                `https://timeapi.io/api/Time/current/zone?timeZone=${encodeURIComponent(zonaHoraria)}`,
                "No se pudo consultar la hora local."
            );
        } catch (e) {
            tiempoLocal = null;
        }

        // API 5: Open-Meteo Air Quality
        let aire = null;

        try {
            aire = await obtenerJson(
                `https://air-quality-api.open-meteo.com/v1/air-quality?latitude=${latitud}&longitude=${longitud}&current=pm2_5,pm10,ozone,uv_index&timezone=auto`,
                "No se pudo consultar la calidad del aire."
            );
        } catch (e) {
            aire = null;
        }

        const actual = clima.current;
        const diario = clima.daily;

        escribir("clima-ciudad", ubicacion.name);
        escribir("clima-pais", ubicacion.country || "País no disponible");
        escribir("clima-temp", Math.round(actual.temperature_2m));
        escribir("clima-icono", climaIcono(actual.weather_code));
        escribir("clima-descripcion", climaTexto(actual.weather_code));
        escribir("clima-fecha", actual.time.replace("T", " "));

        escribir("detalle-termica", `${Math.round(actual.apparent_temperature)} °C`);
        escribir("detalle-max", `${Math.round(diario.temperature_2m_max[0])} °C`);
        escribir("detalle-min", `${Math.round(diario.temperature_2m_min[0])} °C`);
        escribir("detalle-humedad", `${actual.relative_humidity_2m} %`);
        escribir("detalle-viento", `${actual.wind_speed_10m} km/h`);

        escribir("sol-amanecer", formatearHoraDesdeISO(sol.results.sunrise, zonaHoraria));
        escribir("sol-atardecer", formatearHoraDesdeISO(sol.results.sunset, zonaHoraria));

        escribir("dato-latitud", latitud.toFixed(2));
        escribir("dato-longitud", longitud.toFixed(2));
        escribir("dato-zona", zonaHoraria);

        if (tiempoLocal && tiempoLocal.time && tiempoLocal.date) {
            escribir("hora-local", tiempoLocal.time);
            escribir("fecha-local", tiempoLocal.date);
        } else if (tiempoLocal && tiempoLocal.dateTime) {
            const fecha = new Date(tiempoLocal.dateTime);

            escribir("hora-local", fecha.toLocaleTimeString("es-EC", {
                hour: "2-digit",
                minute: "2-digit"
            }));

            escribir("fecha-local", fecha.toLocaleDateString("es-EC", {
                day: "2-digit",
                month: "long",
                year: "numeric"
            }));
        } else {
            escribir("hora-local", "No disponible");
            escribir("fecha-local", "No disponible");
        }

        if (aire && aire.current) {
            escribir("pm25", `${aire.current.pm2_5 ?? "No disponible"} µg/m³`);
            escribir("pm10", `${aire.current.pm10 ?? "No disponible"} µg/m³`);
            escribir("ozono", `${aire.current.ozone ?? "No disponible"} µg/m³`);
            escribir("uv", aire.current.uv_index ?? "No disponible");
        } else {
            escribir("pm25", "No disponible");
            escribir("pm10", "No disponible");
            escribir("ozono", "No disponible");
            escribir("uv", "No disponible");
        }

        escribir("wiki-resumen", wiki.extract || "Descripción no disponible.");
        asignarHref("wiki-link", wiki.content_urls?.desktop?.page || "https://es.wikipedia.org");

        if (resultado) {
            resultado.classList.remove("hidden");
        }

        localStorage.setItem("ultimaCiudad", ubicacion.name);
    } catch (e) {
        mostrarError(e.message);
    } finally {
        if (carga) carga.classList.add("hidden");
        if (btn) btn.disabled = false;
    }
}

if (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const ciudad = input.value.trim();

        if (!ciudad) {
            mostrarError("Escribe una ciudad.");
            return;
        }

        buscar(ciudad);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const ciudad = localStorage.getItem("ultimaCiudad") || "Guayaquil";

    if (input) {
        input.value = ciudad;
    }

    buscar(ciudad);
});
