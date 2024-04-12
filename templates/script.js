$(document).ready(function(){

	// обновление списка
	function loadTableData(forceChange) {
		// выясняем текущую страницу
		let pageNum = $(".page-item.active .page-link").attr("data-page");
		let pageCount = $(".page-item .page-link").length;
		let elemsCount = $(".pagination").attr("data-elemsCount");

		// сам запрос
		$.ajax({
			type: 'POST',
			url: '/ajax/get_msg.php',
			dataType: "json",
			data: {
				action: "getList",
				page: pageNum,
				elemsCount: elemsCount,
				forceChange: forceChange
			},
			success: function(response) {
				if(response.status === true)
				{
					console.log("update list");
					$('.table__autoload').html("");
					$.each(response.data, function(index, item){
						if(item.updateDate === null)
						{
							item.updateDate = "";
						}
						$('.table__autoload').append('' +
							'<tr class="msg">' +
								'<td>'+ parseInt((pageNum * 5) + index - 5 + 1) +'</td>' +
								'<td class="elemName">'+ item.name +'</td>' +
								'<td>'+ item.insertDate +'</td>' +
								'<td>'+ item.updateDate +'</td>' +
								'<td><a class="updateIcon" data-toggle="modal" data-target="#myModalUpdate" data-elemID = "'+ item.id +'"><img src="/templates/settings.png"></a></td>' +
							'</tr>');
					});
					$(".pagination").attr("data-elemsCount", response.newElemsCount);

					// обновление пагинации
					if(pageCount != response.pageData)
					{
						console.log("pages diff");
						$(".pagination").html("");
						for(let page = 1; page <= response.pageData; page++)
						{
							$(".pagination").append('<li class="page-item">' +
								'<a class="page-link" href="#" data-page="'+ page +'">'+ page +'</a>' +
								'</li>');
							$(".pagination .page-link[data-page='"+ pageNum +"']").closest("li").addClass("active");
						}
					}
				}
			}
		});
	}

	// Обновлять данные каждые 5 секунд, но не сразу после загрузки страницы
	setTimeout(function() {
		setInterval(function() {
			loadTableData("N");
		}, 5000);
	}, 5000);


	// открытие модального окна для редактирования
	$(document).on("click", ".page-item", function(e){
		e.preventDefault();

		$(".page-item").removeClass("active");
		$(this).addClass("active");
		loadTableData("Y");

	});
	$(document).on("click", ".updateIcon", function(e){
		e.preventDefault();

		// берем текущий элемент нажатия, и получаем значение поля name
		let elementID = $(this).attr("data-elemID");
		let elementNameObject = $(this).closest(".msg");
		let elementName = elementNameObject.find(".elemName").html();

		// обновляем модальное для редактирования
		$("#myModalUpdate .elem__id").html(elementID);
		$("#myModalUpdate #nameUpdate").val(elementName);
		$("#myModalUpdate #elemIDUpdate").val(elementID);
	});

	function clearUpdateModal()
	{
		$("#myModalUpdate .elem__id").html("");
		$("#myModalUpdate #nameUpdate").val("");
		$("#myModalUpdate #elemIDUpdate").val("");
	}

	// функция обновления
	$(document).on("click", ".update__data", function(e){
		e.preventDefault(); // предотвращаем отправку формы по умолчанию

		var nameUpdate = $('#nameUpdate').val();
		var elemIDUpdate = $('#elemIDUpdate').val();

		// Производим базовую проверку - не пустые ли поля
		if(nameUpdate === ''){
			alert('Поля не могут быть пустыми');
			return;
		}

		// Отправляем данные на сервер
		$.ajax({
			type: 'POST',
			url: '/ajax/update_msg.php',
			dataType: "json",
			data: {
				update_array: {
					name: nameUpdate,
				},
				id: elemIDUpdate
			},
			success: function(response){
				// В случае успеха можно обновить таблицу с данными
				if(response.status === true)
				{
					loadTableData("Y");
					clearUpdateModal();
					$('#myModalUpdate').modal('hide'); // закрываем модальное окно
				}
				else
				{
					alert(response.data.errorMsg);
				}
			},
			error: function(){
				alert('Произошла ошибка при сохранении данных');
			}
		});
	});

	// функция добавления
	$(document).on("click", ".save__data", function(e){
		e.preventDefault(); // предотвращаем отправку формы по умолчанию

		var name = $('#name').val();

		// Производим базовую проверку - не пустые ли поля
		if(name === ''){
			alert('Поля не могут быть пустыми');
			return;
		}

		// Отправляем данные на сервер
		$.ajax({
			type: 'POST',
			url: '/ajax/save_msg.php',
			dataType: "json",
			data: {
				name: name,
			},
			success: function(response){
				// В случае успеха можно обновить таблицу с данными
				if(response.status === true)
				{
					loadTableData("Y");
					$('#myModal').modal('hide'); // закрываем модальное окно
				}
				else
				{
					alert(response.data.errorMsg);
				}
			},
			error: function(){
				alert('Произошла ошибка при сохранении данных');
			}
		});
	});
});