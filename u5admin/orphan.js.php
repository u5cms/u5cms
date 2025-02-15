<script>
function loadScript() {
    const script = document.createElement("script");
    script.src = "orphan.src.php?" + new Date().getTime(); // Cache-Busting
    script.type = "text/javascript";
    script.async = true;

    document.body.appendChild(script);

    // Optional: altes Script nach Laden entfernen
    script.onload = () => {
        setTimeout(() => script.remove(), 500);
    };
}

// Alle 1 Sekunde das Skript neu laden
setInterval(loadScript, 1000);
</script>