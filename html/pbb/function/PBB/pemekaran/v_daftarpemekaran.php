<div class="col-md-12">
	<form class="row" id="form_filter">
		<div class="form-group col-md-3">
			<label for="filter_nop">NOP: </label>
			<input type="text" class="form-control" id="filter_nop" name="filter_nop" required>
		</div>
		<div class="col-md-12">
			<button type="submit" name="filter" value="1" class="btn btn-primary bg-maka">Cari</button>
		</div>
	</form>

	<table class="table table-bordered" id="table_result" style="margin-top: 1em">
		<thead>
			<tr>
				<th>ID</th>
				<th>JENIS</th>
				<th>NOP LAMA</th>
				<th>NOP BARU</th>
				<th>TGL UPDATE</th>
				<th>STATUS</th>
			</tr>
		</thead>
		<tbody>
			<tr class="result-initial">
				<th class="text-center" colspan="6">Silakan cari NOP nya</th>
			</tr>
			<tr class="result-not-found" style="display: none">
				<th class="text-center" colspan="6">NOP tidak ditemukan</th>
			</tr>
		</tbody>
	</table>
</div>

<script>
	$(function () {
		
		$(document)
			.on('submit', '#form_filter', function (e) {
				e.preventDefault()

				let formData = new FormData(this)
				let tableResult = $('#table_result')
				let resultInitial = tableResult.find('.result-initial')
				let resultNotFound = tableResult.find('.result-not-found')
				let resultFound = tableResult.find('.result-found')
				
				let resultRow = data => `<tr class="result-found">
					<td>${data.ID}</td>
					<td>${data.JENIS}</td>
					<td>${data.NOP_LAMA}</td>
					<td>${data.NOP_BARU}</td>
					<td>${data.TGL_UPDATE}</td>
					<td>${data.STATUS}</td>
				</tr>`

				let reset = () => {
					tableResult.find('[data-result]').html('')
					resultInitial.hide()
					resultNotFound.hide()
					resultFound.remove()
				}

				reset()

				$.ajax({
					url: 'function/PBB/pemekaran/daftar/data.php',
					method: 'POST',
					data: {
						filter: 1,
						nop: formData.get('filter_nop')
					},
					success: function (response) {
						if (!response.status) {
							resultNotFound.show()
							return
						}

						response.data.forEach(data => {
							tableResult.find('tbody').append(resultRow(data))
						})
					},
					error: function () {
						resultNotFound.show()
					}
				})


				return false
			})
	})
</script>