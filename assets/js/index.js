/* eslint-disable no-undef */
import * as moment from 'moment';
import docReady from 'doc-ready';

const onSubmit = (picker, name) => {
    const btn = document.querySelector(`.btn-import[data-name="${name}"]`);
    btn.classList.add('is-loading');

    const data = new FormData(document.getElementById('comment-form'));
    data.append('trackerName', name);
    data.append('dates', picker.selectedDates.map(date => moment(date).format('D/M/Y')));

    fetch('/submit.php', {
        method: 'post',
        body: data,
    }).then((response) => {
        btn.classList.remove('is-loading');

        if (!response.ok) {
            btn.innerText = `Toevoegen mislukt: Error ${response.statusText}`;
            btn.classList.add('is-warning');
            return false;
        }

        return response.json();
    }).then((json) => {
        if (json.error) {
            btn.innerText = `Toevoegen mislukt: Error ${json.error}`;
            btn.classList.add('is-warning');
            return false;
        }

        btn.innerText = `${json.addedCount} items toegevoegd.`;
    });
};

docReady(async () => {
    const btns = document.getElementsByClassName('btn-import');
    const picker = flatpickr('#datepicker', {
        mode: 'range',
        inline: true,
    });

    Array.from(btns).forEach((btn) => {
        btn.classList.remove('is-loading');
        btn.addEventListener('click', () => onSubmit(picker, btn.dataset.name));
    });
});
