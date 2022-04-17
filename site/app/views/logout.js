document.querySelector('#logout-form button').addEventListener('click', e => {

    e.preventDefault()
    window.location = 'http://localhost:8300/?action=logout&to=' + document.baseURI
})