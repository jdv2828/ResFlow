import './bootstrap';
import Alpine from 'alpinejs';

Alpine.data('fuelFilter', (combustibles, estacionInicial = '', combustibleInicial = '') => ({
    estacionId: estacionInicial,
    combustibleId: combustibleInicial,
    combustibles,

    get filteredCombustibles() {
        return this.estacionId
            ? this.combustibles.filter(c => c.estacion_servicio_id == this.estacionId)
            : this.combustibles;
    },

    syncEstacion() {
        const c = this.combustibles.find(c => c.id == this.combustibleId);
        if (c) this.estacionId = c.estacion_servicio_id;
    },
}));

Alpine.start();
