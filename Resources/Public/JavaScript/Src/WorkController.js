/** todos
    *   fehlermeldungen
    *   ad hoc funktionen benennen
    *   facette entstehungsdatum
    *   t3-buildmechanismus für js
    *   facettenregistratur
    *   urlparameter auf highlighting abbilden
    */

const LISZT_WORK_DEFAULT_COUNT = 20;
const LISZT_WORK_DEFAULT_TRIGGER_POSITION = 200;
const LISZT_WORK_DEFAULT_WORKLIST_ID = 'work';
const LISZT_WORK_DEFAULT_BIBINDEX = 'zotero';
const LISZT_WORK_DEFAULT_LOCALEINDEX = 'zotero';
const LISZT_WORK_LIST = 'liszt-work-list'
const LISZT_WORK_BACK = 'liszt-work-back'

class WorkController {

    #urlManager = null;
    #target = '';
    #size = LISZT_COMMON_MAX_SIZE;
    #triggerPosition = LISZT_WORK_DEFAULT_TRIGGER_POSITION;
    #workListId = LISZT_WORK_DEFAULT_WORKLIST_ID;
    #url = new URL(location);
    #body = {};
    #works = [];
    #sources = {};
    #bibEntries = {};

    constructor (config) {
        this.#target = config.target;
        this.#size = config.size ?? this.#size;
        this.#triggerPosition = config.triggerPosition ?? this.#triggerPosition;
        this.#workListId = config.workListId ?? this.#workListId;
    }

    async init() {
        this.client = new elasticsearch.Client({
            host: 'https://ddev-liszt-portal.ddev.site:9201'
        });
        this.from = 0;
        const response = await this.client.search({ index: 'work', size: this.#size, body: this.#body });
        this.#works = response.hits.hits;
    }

    listAction() {
        $(`#${this.#target}`).html('');
        $(`#${this.#target}`).append(`<ul id="${LISZT_WORK_LIST}" class="list-group"></ul>`);
        this.#works.forEach(doc => this.renderDoc(doc));
    }

    renderDoc(doc) {
        $(`#${LISZT_WORK_LIST}`).append(`<a id="${doc._id}" href="" class="list-group-item list-group-item-action">${doc._id}</li>`);
        $(`#${LISZT_WORK_LIST} #${doc._id}`)
            .click(e => {
                e.preventDefault();
                this.showAction(doc);
            });
    }

    showAction(doc) {
        $(`#${this.#target}`).html('');
        $(`#${this.#target}`).append(`<a id="${LISZT_WORK_BACK}" class="btn btn-primary mb-4" href="">zurück</a>`);
        $(`#${this.#target}`).append(doc._source.content);
        $(`#${LISZT_WORK_BACK}`).click( e => {
            e.preventDefault();
            this.listAction();
        });
        $(`.source`).each( (_, source) => {
            const sourceId = $(source).find('h3').html();
            const target = $(source).parents('.fold');
            this.renderSource(sourceId, target);
        });
        $(`.bibl_record`).each( (_, entry) => {
            const entryId = $(entry).find('i').html();
            const target = $(entry).parents('.fold');
            this.renderBibEntry(entryId, target);
        });

        this.purge();
    }

    purge() {
        // remove hide alternative languages link
        $('div.settings.colophon.noprint').remove();
        // style composer name
        $('p.composer_top').addClass('h2');
        // margin above section headings
        $('h3.section_heading').addClass('mt-4');
        // style expression borders
        $('<hr>').insertBefore('h2.expression_heading');
        // make section headings clickable
        $('h3.section_heading').each( (_, heading) => {
            const headingText = $(heading).html();
            $(heading).html(`<a href=""> ${headingText} </a>`);
        });
    }

    async renderSource(sourceId, target) {
        if (!this.#sources[sourceId]) {
            this.#sources[sourceId] = await this.client.get({index: 'source', id: sourceId});
        }
        const source = this.#sources[sourceId]['_source'];
        const composer = source[100][0];
        const composerString = `${composer['a'][0][0]} (${composer['d'][0][0]})`;
        const title = source[240][0];
        const titleString = `${title['a'][0][0]} (${title['n'].map(d => d[0]).join(', ')})`;
        const descriptions = source[500].map(d => ({title: d['a'][0][0].split(':')[0], text: d['a'][0][0].split(':')[1]}));
        const descriptionsString = descriptions.map(d => `<li class="list-group-item"><h4>${d.title}</h4><p>${d.text}</p></li>`).join('');

        const librarySiglum = source[852][0]['a'][0][0];
        const shelfmark = source[852][0]['c'][0][0];
        const library = source[852][0]['e'][0][0];
        const libString = `<h4 class="my-3 source-title"><a href="">+ ${shelfmark}, ${librarySiglum} (${library})</a></h4>`;

        const sourceString = `<div id="${sourceId}" class="source-paragraph">${libString}<ul class="list-group source-list">${descriptionsString}</ul></div>`;

        target.append(sourceString);
        const sourceElement = $(`#${sourceId}`);
        const listElement = sourceElement.find(`ul.source-list`);
        listElement.hide();
        sourceElement.find(`h4.source-title a`).click(e => {
            e.preventDefault();
            listElement.slideToggle();
        });
        sourceElement.hide();
        sourceElement.parents('div.fold').find('h3.section_heading a').click(e => {
            e.preventDefault();
            sourceElement.slideToggle()
        });
    }

    async renderBibEntry(entryId, target) {
        if (!this.#bibEntries[entryId]) {
            this.#bibEntries[entryId] = await this.client.get({index: 'zotero', id: entryId});
        }
        const entry = this.#bibEntries[entryId]['_source'];
        const author = entry.creators.filter(creator => creator.creatorType == 'author')
            .map(creator => `${creator.firstName} ${creator.lastName}`)
            .join(', ');
        const title = entry.title;
        const entryString = `<div id="${entryId}">${author}: ${title}</div>`;
        target.append(entryString);
    }

}
