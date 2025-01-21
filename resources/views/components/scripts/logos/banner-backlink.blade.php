<style>
    #link_{{ $id }} {
        position: absolute;
        right: 0;
        top: 0;
        height: 20px;
        color: gold;
		background-color: grey;
        font-family: Verdana, Arial, sans-serif;
        font-size: 12px;
        text-decoration: none;
        font-weight: bold;
		padding: 1px 0 0 0;
		border: solid 1px
    }
    #link_{{ $id }} > img {
        vertical-align: middle;
        height: 100%;
        margin: 0
    }
    #link_{{ $id }} > span {
        display: none
    }
    #link_{{ $id }}:hover {
        height: 20px;
		background-color: grey;
        padding: 1px 0 0 6px;
		border: solid 1px
    }
    #link_{{ $id }}:hover span {
        display: inline
    }
</style>
<a href="{{ config('app.url') }}" target="_blank" id="link_{{ $id }}">
    <span>ADS By {{ config('app.name') }}</span>
    <img alt="{{ config('app.name') }}" src="data:image/png;base64,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">
</a>