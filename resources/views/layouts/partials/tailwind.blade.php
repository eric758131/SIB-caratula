{{--
    Tailwind (CDN) con el color de marca de la S.I.B.
    "brand" es el mismo verde del asistente de Angular (#16a34a = brand-600).
    Para cambiar el color de todo el panel basta con cambiar esta paleta.
--}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac', 400: '#4ade80',
                        500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d', 950: '#052e16',
                    },
                },
                fontFamily: {
                    sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Arial', 'sans-serif'],
                },
            },
        },
    };
</script>
<style type="text/tailwindcss">
    @layer base {
        /* Tablas del panel: encabezado más marcado y filas más legibles */
        main table thead th { @apply bg-slate-50 text-slate-500; }
        main table tbody tr:hover { @apply bg-brand-50/40; }

        /* Campos: mismo foco verde en todos los formularios */
        main input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        main select,
        main textarea { @apply rounded-lg transition focus:border-brand-500; }
        main input[type="file"] { @apply file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100; }

        /* Tarjetas y botones algo más redondeados */
        main .rounded-lg.shadow { @apply rounded-xl shadow-sm ring-1 ring-slate-200/70; }
        main a.rounded, main button.rounded { @apply rounded-lg; }

        button:disabled { @apply cursor-not-allowed; }
    }
</style>
