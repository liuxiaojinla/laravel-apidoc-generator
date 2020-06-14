<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ config('apidoc.laravel.title') ?? config('app.name')." API" }}</title>
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Styles -->
	<link rel="stylesheet" href="{{ asset('vendor/apidoc/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('vendor/apidoc/highlight/styles/vs2015.css') }}">
	<link rel="stylesheet" href="{{ asset('vendor/apidoc/apidoc.css') }}">

	<!-- Script -->
	<script src="{{ asset('vendor/apidoc/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('vendor/apidoc/sortable/Sortable.min.js') }}"></script>
	<script src="{{ asset('vendor/apidoc/vue/vue.min.js') }}"></script>
	<script src="{{ asset('vendor/apidoc/vue/vuedraggable.min.js') }}"></script>
	<script src="{{ asset('vendor/apidoc/highlight/highlight.pack.js') }}"></script>
</head>
<body>
<div class="container" id="app">
	<div class="row examples">
		<div class="col-md-3 aside">
			<div class="aside-info">
				<div class="title mb-3">
					{{ config('apidoc.laravel.title') ?? config('app.name')." API" }}
				</div>
				<div class="input-group">
					<input type="text" class="form-control" v-model="search" placeholder="请输入API名称">
					<div class="input-group-append">
					<span class="input-group-text">
						<svg class="bi bi-search" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z"/>
						<path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/>
					</svg>
					</span>
					</div>
				</div>
			</div>
			@verbatim
				<ul class="nav flex-column">
					<li v-for="group in items" class="nav-item">
						<a href="javascript:void(0)" class="nav-link disabled"><strong>{{ group.name }}</strong></a>

						<ul class="nav flex-column">
							<li v-for="item in group.item" class="nav-item"
									v-show="isShouldShowApi(item)"
									@click="onAsideItemClick(item)">
								<a href="javascript:void(0)" class="nav-link"
										:id="'r'+item._postman_id"
										:class="{active:isAsideItemActive(item)}">{{ item.name }}</a>
							</li>
						</ul>
					</li>
				</ul>
			@endverbatim
		</div>

		<div class="col-md-9" style="padding-top: 30px;padding-bottom: 30px">
			@verbatim
				<template v-if="chooseItem">
					<h1>{{ chooseItem.name }}</h1>
					<p class="lead">{{ chooseItem.request.description }}</p>

					<p>
						<span class="badge badge-success">{{ chooseItem.request.method }}</span>
						{{ chooseItem.request.url.protocol }}://{{ chooseItem.request.url.host }}/{{ chooseItem.request.url.path }}
						<span class="badge badge-danger" v-show="chooseItem.request.authenticated">需要用户授权</span>
					</p>

					<p>
						<a data-toggle="collapse" href="#header-detail">
							<strong>请求头:</strong>
						</a>
					</p>
					<div class="collapse" id="header-detail">
						<pre class="hljs"><code>{{ chooseItem.request.header }}</code></pre>
					</div>

					<p><strong>请求:</strong></p>
					<ul class="nav nav-tabs">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#url_params_tpl" role="tab">路由参数</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#query_params_tpl" role="tab">请求参数</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#body_params_tpl" role="tab">表单参数</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane fade show active" id="url_params_tpl" role="tabpanel">
							<table class="table">
								<thead>
								<tr>
									<th width="120">参数名</th>
									<th width="200">示例值</th>
									<th width="60">必填</th>
									<th>描述</th>
								</tr>
								</thead>
								<tbody>
								<tr v-for="(param,key) in chooseItem.request.url_params_tpl">
									<td>{{ key }}</td>
									<td>{{ param.value }}</td>
									<td style="text-align: center">{{ param.required?'是':'否' }}</td>
									<td>{{ param.description }}</td>
								</tr>
								</tbody>
							</table>
						</div>

						<div class="tab-pane fade" id="query_params_tpl" role="tabpanel">
							<table class="table">
								<thead>
								<tr>
									<th width="120">参数名</th>
									<th width="200">示例值</th>
									<th width="60">必填</th>
									<th>描述</th>
								</tr>
								</thead>
								<tbody>
								<tr v-for="param in chooseItem.request.url.query">
									<td>{{ param.key }}</td>
									<td>{{ param.value }}</td>
									<td style="text-align: center">{{ param.required?'是':'否' }}</td>
									<td>{{ param.description }}</td>
								</tr>
								</tbody>
							</table>
						</div>

						<div class="tab-pane fade" id="body_params_tpl" role="tabpanel">
							<table class="table">
								<thead>
								<tr>
									<th width="120">参数名</th>
									<th width="80">类型</th>
									<th width="200">示例值</th>
									<th width="60">必填</th>
									<th>描述</th>
								</tr>
								</thead>
								<tbody>
								<tr v-for="(param,key) in chooseItem.request.body_params_tpl">
									<td>{{ key }}</td>
									<td>{{ param.type }}</td>
									<td>{{ param.value }}</td>
									<td style="text-align: center">{{ param.required?'是':'否' }}</td>
									<td>{{ param.description }}</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>

					<template v-if="chooseItem.request.response.length">
						<p><strong>响应:</strong></p>
						<pre class="hljs"><code>{{ resolveResponse() }}</code></pre>
					</template>

				</template>
			@endverbatim
			<template v-else>
				<div class="logo">
					<img src="{{ config('apidoc.logo') }}"/>
				</div>
				<h1 class="text-center display-1">
					{{ config('apidoc.laravel.title') ?? config('app.name')." API" }}
				</h1>
			</template>
		</div>

	</div>
</div>
</body>
<script src="{{ asset('vendor/apidoc/apidoc.js') }}"></script>
</html>
