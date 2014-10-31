
from urllib import request, error


class BulkLoader(object):
	def __init__(self, endpoint, outdir):
		self.endpoint = endpoint
		self.outdir = outdir

	def load_resource(self, url):
		try:
			res = request.urlopen(url)
			return res.read()
		except error.HTTPError as e:
			return None
	
	def load_tile(self, z, x, y):
		print('Load tile %d, %d' % (x, y))
		url_model = self.endpoint + '/%d/%d/%d.xml' % (z, x, y)
		url_asset = self.endpoint + '/%d/%d/%d-asset.xml' % (z, x, y)
		url_texture = self.endpoint + '/%d/%d/%d-texture.xml' % (z, x, y)
		model = self.load_resource(url_model)
		print(model)
		asset = self.load_resource(url_asset)
		print(asset)
		texture = self.load_resource(url_texture)
		print(texture)

	
	def load_tile_range(self, zoom, min, max):
		for x in range(min[0], max[0]+1):
			for y in range(min[1], max[1]+1):
				self.load_tile(zoom, x, y)


if __name__ == "__main__":
	import sys
	
	endpoint = 'http://130.206.80.175/api/3d-map-tiles/sb'
	outdir = 'output'
	
	zoom = 18
	bbox = (136189, 89735, 136206, 89746)
	
	loader = BulkLoader(endpoint, outdir)
	loader.load_tile_range(zoom, bbox[0:2], bbox[2:4])
	
	
	
	