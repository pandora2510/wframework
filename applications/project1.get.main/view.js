w.area.action['project1.get.main'] = function(act,data,l10n) {
    act.innerHTML = w('#project1.get.main-body').template({arg:{data:data}});
}
