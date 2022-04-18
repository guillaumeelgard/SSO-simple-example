document.querySelector('#logout-form button').addEventListener('click', e => {

    e.preventDefault()
    window.location = '<?=$authAddress?>/?action=logout&to=' + document.baseURI
})