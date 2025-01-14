const Inicio = {

	userNome: '',
	setNomePop: null,

	pathAjax:       _CONFIG.rootURL +'inicio/ajax.php',
	pathFields:     _CONFIG.rootURL +'inicio/fields.json',
	pathAoSelConta: _CONFIG.rootURL +'produtos/',

	init: callback => {

		Inicio.getInit(() => {

			Inicio.initForm();

			Inicio.initFields();

			if(typeof callback == 'function') callback();
		});

	},


	getInit: callback => {
		let params = {
			com: 'getInit'
		};
		R4.getJSON(Inicio.pathAjax, params)
		.then(ret => {

			Inicio.setHTMLNome(ret.dados.userNome);

			Inicio.setListaContas(ret.contas);

			if(typeof callback == 'function') callback();
		});
	},


	initForm: () => {
		Dialog.create({
			elem: $('#formCriarConta'),
			title: 'Criar nova conta',
			onOpen: function(){
				$('#inicioNomeConta').val('');
			},
			buttons: [
				{
					label:   'Criar nova conta',
					classes: 'bgSuccess',
					onClick: function(){
						Inicio.inserirConta();
					}
				},
				{
					label:   'Voltar',
					classes: 'R4DialogCloser bgDanger'
				}
			]
		});
	},


	initFields: () => {
		Fields.create([
			{ id: 'inicioNomeConta', type: 'string', maxSize: 200 }
		]);
	},


	abrirFormCriarConta: () => {
		Dialog.open($('#formCriarConta'));
	},


	setHTMLNome: nome => {

		if(nome) {
			$('.labelUserNome').innerHTML = nome;
		} else {
			$('.labelUserNome').innerHTML = 'desconhecido <small><a href="#" id="linkTenhoNome">'
			                              + 'Ei, eu tenho nome</a></small>';

			Pop.click($('#linkTenhoNome'), {
				preventDefault: true,
				classes: 'paspatur',
				html: '<div id="inputUserNome">Diga seu nome</div><div id="btnUserNome">Gravar</div>',
				onOpen: function(){
					Fields.create([
						{ id:'inputUserNome', type:'string' },
						{ id:'btnUserNome',   type:'button', classes:'bgSuccess' }
					]);

					$('#inputUserNome').focus();

					$('#btnUserNome').on('click', function(){
						let nome = $('#inputUserNome').val();
						if(nome) {
							Pop.destroyAll('force');
							Inicio.salvarNome(nome);
						} else {
							Warning.show('Preencha seu nome antes de clicar no botão');
						}
					});
				}
			});
		}
	},


	salvarNome: nome => {
		if(!nome) return;

		R4.getJSON(Inicio.pathAjax, {
			com: 'salvarNome',
			val: nome
		})

		.then(ret => {
			Inicio.setHTMLNome(ret.dados.userNome);
		})
	},


	setListaContas: dados => {
		for(let k in dados) {
			Inicio.addHTMLConta(dados[k]);
		}
	},


	inserirConta: () => {

		R4.getJSON(Inicio.pathAjax, {
			com: 'inserirConta',
			nome: $('#inicioNomeConta').val()
		})

		.then(ret => {
			Inicio.addHTMLConta(ret.dados);
			Dialog.close($('#formCriarConta'));
		});
	},


	addHTMLConta:  dados => {
		let elem = $new(
			'<div class="linhaConta" idConta="'+ dados.id +'">'+
				'<div>'+ dados.id                    +'</div>'+
				'<div>'+ dados.nome                  +'</div>'+
				'<div>'+ R4.dateMask(dados.dtAcesso) +'</div>'+
			'</div>'
		);

		elem.on('click', function(ev) {

			R4.getJSON(Inicio.pathAjax, {
				com: 'selConta',
				id:  this.attr('idConta')
			})

			.then(ret => {
				window.location = Inicio.pathAoSelConta;
			});
		});

		$('#boxContas').append(elem);
	}

};