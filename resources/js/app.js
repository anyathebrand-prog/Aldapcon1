import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

// TRD §2.1 — Alpine covers the handful of genuinely interactive surfaces
// without a second language or client-side state management. Livewire arrives
// in Phase 5 for the admin tables and Phase 9a for the multi-step signup.
//
// The focus plugin provides x-trap, which UI brief §5.3 and §10 require: the
// mobile menu traps focus while open, returns it to the trigger on close, and
// locks background scroll. Hand-rolling that correctly is more code than the
// plugin costs, and getting it subtly wrong is invisible until somebody
// navigates by keyboard.

Alpine.plugin(focus);

window.Alpine = Alpine;

Alpine.start();
