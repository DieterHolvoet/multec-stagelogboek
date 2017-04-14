/* eslint-disable no-undef */
import * as moment from 'moment';
import docReady from 'doc-ready';

const onSubmit = (picker) => {
    const btn = document.getElementById('btn-import');
    btn.classList.add('is-loading');

    const data = new FormData(document.getElementById('comment-form'));
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
        btn.innerText = `${json.count} items toegevoegd.`;
    });
};

docReady(async () => {
    const btn = document.getElementById('btn-import');
    const picker = flatpickr('#datepicker', {
        mode: 'range',
        inline: true,
    });

    btn.classList.remove('is-loading');
    btn.addEventListener('click', () => onSubmit(picker));
});
