</main>
</div>

<!-- Shim modal vanilla (sustituye a bootstrap.bundle.min.js) -->
<script>
window.bootstrap = window.bootstrap || {};
if (!window.bootstrap.Modal) {
  class VanillaModal {
    constructor(el, opts={}) {
      this.el = el;
      this.opts = {backdrop:true, keyboard:true, ...opts};
      this._esc = this._esc.bind(this);
      this.el.addEventListener('click', (e)=>{
        if (e.target.matches('[data-bs-dismiss="modal"], .btn-close')) this.hide();
      });
    }
    _makeBackdrop(){
      if (!this.opts.backdrop) return null;
      const bd = document.createElement('div');
      bd.className = 'modal-backdrop';
      document.body.appendChild(bd);
      if (this.opts.backdrop === true) {
        bd.addEventListener('click', ()=> this.hide());
      }
      return bd;
    }
    show(){
      if (this._shown) return;
      this._shown = true;
      this._bd = this._makeBackdrop();
      document.body.style.overflow = 'hidden';
      this.el.classList.add('show');
      this.el.style.display = 'block';
      if (this.opts.keyboard) document.addEventListener('keydown', this._esc);
    }
    hide(){
      if (!this._shown) return;
      this._shown = false;
      this.el.classList.remove('show');
      this.el.style.display = 'none';
      if (this._bd) this._bd.remove();
      document.body.style.overflow = '';
      document.removeEventListener('keydown', this._esc);
    }
    _esc(e){ if (e.key === 'Escape') this.hide(); }
  }
  window.bootstrap.Modal = VanillaModal;
}
</script>
