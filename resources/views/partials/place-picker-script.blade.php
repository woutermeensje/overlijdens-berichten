{{--
    Plaatskiezer met suggesties uit de PDOK Locatieserver (alleen Nederlandse woonplaatsen).
    Markup binnen een <form>:
      <div data-place-picker>
        <input type="text" data-place-input>        zichtbare plaatsnaam
        <input type="hidden" data-place-id>          PDOK-id van de gekozen woonplaats (server controleert via Geocoder::placeById)
        <ul data-place-list></ul>                    suggestielijst
        <p data-place-error class="hidden">…</p>     melding als er geen plaats uit de lijst is gekozen
        <p data-place-details></p>                   optioneel: toont "Provincie · Nederland" na een keuze
      </div>
--}}
@once
    <script>
        (() => {
            const SUGGEST_URL = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest';

            const escapeHtml = (value) => value.replace(/[&<>"']/g, (c) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

            document.querySelectorAll('[data-place-picker]').forEach((picker) => {
                const form = picker.closest('form');
                const input = picker.querySelector('[data-place-input]');
                const hiddenId = picker.querySelector('[data-place-id]');
                const list = picker.querySelector('[data-place-list]');
                const error = picker.querySelector('[data-place-error]');
                const details = picker.querySelector('[data-place-details]');
                let places = [];
                let active = -1;
                let timer = null;
                let controller = null;
                let confirmedLabel = hiddenId.value ? input.value : null;

                const showDetails = (province) => {
                    if (!details) return;
                    details.textContent = province ? `${province} · Nederland` : '';
                    details.classList.toggle('hidden', !province);
                };

                const close = () => {
                    list.classList.add('hidden');
                    input.setAttribute('aria-expanded', 'false');
                    active = -1;
                };

                const render = () => {
                    if (places.length === 0) {
                        list.innerHTML = '<li class="menu-disabled"><span>Geen plaats gevonden</span></li>';
                    } else {
                        list.innerHTML = places.map((place, i) =>
                            `<li role="option" aria-selected="${i === active}"><a class="${i === active ? 'menu-active' : ''}" data-index="${i}">${escapeHtml(place.weergavenaam)}</a></li>`
                        ).join('');
                    }
                    list.classList.remove('hidden');
                    input.setAttribute('aria-expanded', 'true');
                };

                const choose = (place) => {
                    input.value = place.woonplaatsnaam;
                    hiddenId.value = place.id;
                    confirmedLabel = input.value;
                    error.classList.add('hidden');
                    input.classList.remove('input-error');
                    showDetails(place.provincienaam);
                    close();
                    input.dispatchEvent(new Event('change', {bubbles: true}));
                };

                const search = async (query) => {
                    controller?.abort();
                    controller = new AbortController();
                    const params = new URLSearchParams({q: query, fq: 'type:woonplaats', rows: '8', fl: 'id,weergavenaam,woonplaatsnaam,provincienaam'});
                    try {
                        const response = await fetch(`${SUGGEST_URL}?${params}`, {signal: controller.signal});
                        const data = await response.json();
                        places = data.response?.docs ?? [];
                        active = places.length ? 0 : -1;
                        render();
                    } catch (e) {
                        if (e.name !== 'AbortError') close();
                    }
                };

                input.addEventListener('input', () => {
                    if (input.value !== confirmedLabel) {
                        hiddenId.value = '';
                        showDetails(null);
                    }
                    clearTimeout(timer);
                    const query = input.value.trim();
                    if (query.length < 2) {
                        close();
                        return;
                    }
                    timer = setTimeout(() => search(query), 200);
                });

                input.addEventListener('keydown', (event) => {
                    if (list.classList.contains('hidden') || places.length === 0) return;
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        active = (active + (event.key === 'ArrowDown' ? 1 : -1) + places.length) % places.length;
                        render();
                    } else if (event.key === 'Enter' && active >= 0) {
                        event.preventDefault();
                        choose(places[active]);
                    } else if (event.key === 'Escape') {
                        close();
                    }
                });

                // mousedown in plaats van click, zodat de keuze vóór de blur van het invoerveld valt.
                list.addEventListener('mousedown', (event) => {
                    const item = event.target.closest('[data-index]');
                    if (!item) return;
                    event.preventDefault();
                    choose(places[Number(item.dataset.index)]);
                });

                input.addEventListener('blur', close);

                form?.addEventListener('submit', (event) => {
                    if (input.value.trim() !== '' && hiddenId.value === '') {
                        event.preventDefault();
                        error.classList.remove('hidden');
                        input.classList.add('input-error');
                        input.focus();
                    }
                });
            });
        })();
    </script>
@endonce
